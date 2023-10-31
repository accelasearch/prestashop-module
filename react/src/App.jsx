import { t } from "./utils";

export default function App() {
  return (
    <main>
      <h1 className="text-red-500 font-bold">
        {_AS.userStatus.logged ? <span>Logged</span> : <span>Not logged</span>}
      </h1>
      {t("Submit")}
      {t("Inexistent translation")}
    </main>
  );
}
