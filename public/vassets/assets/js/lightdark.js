let dark = localStorage.getItem("dark");
const toggle = document.getElementById("mode-toggle");

const enableDark = () => {
  window
    .getComputedStyle(document.documentElement)
    .getPropertyValue("--background");

  document.documentElement.style.setProperty("--backgrounddd", "#060913");
  document.documentElement.style.setProperty("--primaryyy", "#0938ae");
  document.documentElement.style.setProperty("--secondaryyy", "#FEF3F1");
  document.documentElement.style.setProperty("--accenttt", "#1FC173");
  document.documentElement.style.setProperty("--texttt", "#E1E7F4");
  document.documentElement.style.setProperty("--text-liteee", "#999");
  document.documentElement.style.setProperty("--card-bggg", "060c1b");
  document.documentElement.style.setProperty("--w-bggg", "#000");
  document.documentElement.style.setProperty("--primary-liteee", "#1276e96b");
  document.documentElement.style.setProperty("--borderrr", "#222a43");

  toggle.innerHTML = '<i class="fa fa-regular fa-sun"></i>';

  localStorage.setItem("dark", "enabled");
};

const disableDark = () => {
  window
    .getComputedStyle(document.documentElement)
    .getPropertyValue("--background");

  document.documentElement.style.setProperty("--backgrounddd", "#eceff9");
  document.documentElement.style.setProperty("--primaryyy", "#1276e9");
  document.documentElement.style.setProperty("--secondaryyy", "#0e0301");
  document.documentElement.style.setProperty("--accenttt", "#FEF3F1");
  document.documentElement.style.setProperty("--texttt", "#0b101d");
  document.documentElement.style.setProperty("--text-liteee", "#999");
  document.documentElement.style.setProperty("--card-bggg", "#fff");
  document.documentElement.style.setProperty("--w-bggg", "#fff");
  document.documentElement.style.setProperty("--primary-liteee", "#1276e96b");
  document.documentElement.style.setProperty("--borderrr", "#e6edef");

  toggle.innerHTML = '<i class="fa fa-regular fa-moon"></i>';

  localStorage.setItem("dark", null);
};

if (dark === "enabled") {
  enableDark();
}

toggle.addEventListener("click", () => {
  dark = localStorage.getItem("dark");
  //   console.log(dark);

  if (dark !== "enabled") {
    enableDark();
  } else {
    disableDark();
  }
});
