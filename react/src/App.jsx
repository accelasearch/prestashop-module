import AsApp from "./pages/AsApp";
import Welcome from "./pages/Welcome";
import { Toaster } from "react-hot-toast";

export default function App() {
  return (
    <main className="relative min-h-full py-8 px-4 sm:px-6 lg:px-8 rounded bg-white">
      <Toaster containerStyle={{ position: "absolute" }} />
      {_AS.userStatus.logged ? <AsApp /> : <Welcome />}
    </main>
  );
}
