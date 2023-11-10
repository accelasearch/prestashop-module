import AsApp from "./pages/AsApp";
import Welcome from "./pages/Welcome";
import { Toaster } from "react-hot-toast";
import { cx } from "./utils";
import { useSelector } from "react-redux";

export default function App() {
  const onBoarding = useSelector((state) => state.user.userStatus.onBoarding);
  return (
    <main
      className={cx(
        onBoarding !== 3 && "py-8 px-4 sm:px-6 lg:px-8",
        "relative min-h-full rounded bg-white"
      )}
    >
      <div>
        <Toaster containerStyle={{ position: "absolute" }} />
      </div>
      {_AS.userStatus.logged ? <AsApp /> : <Welcome />}
    </main>
  );
}
