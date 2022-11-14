const args = require("args-parser")(process.argv);
const { exec } = require("child_process");
const { zip } = require("zip-a-folder");
const rimraf = require("rimraf");
const fs = require("fs");
const chalk = require("chalk");
const replace = require("replace-in-file");

const _error = chalk.keyword("red");
const _warning = chalk.keyword("orange");
const _success = chalk.keyword("green");
const _command = chalk.bgCyan.bold;

const showHelp = () => {
  const helpText = `

    Guida all'utilizzo dei comandi

    ${_command(
      'npx . create-package --version="1.0.0"'
    )} => Crea una release del modulo

    Comporta la modifica del file principale del modulo, cambiando la versione effettiva riconosciuta
    del modulo e la creazione del bundle di produzione dentro la cartella ${_success(
      "releases"
    )} e la pubblicazione di una nuova release sul github remoto.
    Controlla l'esistenza del changelog della versione che si sta cercando di rilasciare.

    ${_command(
      'npx . create-package --version="1.0.0" --upgrade-db-version="1.0.0"'
    )} => Crea una release del modulo ed aggiorna il Database

    Uguale alla precedente, ma con il parametro ${_success(
      "--upgrade-db-version"
    )} possiamo specificare
    che anche il Database e la sua struttura sono cambiati, fondamentale per tenere traccia della
    versione del Db

    PARAMETRI OPZIONALI

    ${_command(
      "--release-message"
    )} => Aggiunge un messaggio alla release remota di Github

    ${_command("--git-release")} => Crea la release su Github. Default: true

  `;
  console.log(helpText);
};

const createPackageZip = async (version) => {
  await fs.mkdir("releases/versions/" + version, () => null);
  await zip(
    "releases/tmp_dir/",
    "releases/versions/" + version + "/accelasearch.zip"
  );
  rimraf("releases/tmp_dir/*", () => console.log("Pacchetto grezzo eliminato"));
};

const existChangelog = async (version) => {
  const changelog = await fs.readFileSync("./CHANGELOG.md", {
    encoding: "UTF8",
  });
  return new RegExp("## \\[" + version + "\\] -.*").test(changelog);
};

const createPackage = async (version, message = "") => {
  if (!version) {
    console.log(
      _error("Fatal error: Specifica la versione della release con --version")
    );
    return;
  }
  const changelogExist = await existChangelog(version);
  if (!changelogExist) {
    console.log(
      _error(
        "Fatal error: Specifica nel file CHANGELOG.md cosa è cambiato in questa versione prima di fare una release"
      )
    );
    return;
  }
  const release_to_git = args["git-release"] !== "false";
  console.log(`Inizio la creazione del pacchetto v ${version}`);
  try {
    const options = {
      files: "./accelasearch.php",
      from: [
        /\* @version .*/g,
        /\$this->version = '.*'/g,
        /"DEBUG_MODE" => true/g,
      ],
      to: [
        "* @version " + version,
        "$this->version = '" + version + "'",
        '"DEBUG_MODE" => false',
      ],
    };
    await replace(options);
  } catch (error) {
    console.error(_error("Error occurred:", error));
  }
  exec(
    "npx copyfiles -e './sample_structure.png' -e './classes/Updater/updater_uml.png' ./classes/*.php ./classes/Updater/** ./controllers/** ./sql/** ./views/** ./*.php ./*.png releases/tmp_dir/accelasearch",
    async () => {
      console.log("Pacchetto grezzo creato");
      await createPackageZip(version);
      if (release_to_git) {
        exec(
          `github-release upload \
          --owner buggyzap \
          --repo accelasearch \
          --tag "v${version}" \
          --release-name "AccelaSearch v${version}" \
          --body "${message}" \
          ./releases/versions/${version}/accelasearch.zip`,
          () => console.log("Release creata su Github")
        );
      }
    }
  );
};

if (args["create-package"]) {
  const { version, "release-message": message } = args;
  createPackage(version, message);
}

if (args.help) {
  showHelp();
  return;
}
