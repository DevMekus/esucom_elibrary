import { CONFIG } from "./core/Config.js"
import Utility from "./core/Utility.js"


document.addEventListener('DOMContentLoaded', () => {

    
})


function displayFooterYear() {
    const domEl = document.getElementById("year");
    if (domEl) domEl.textContent = new Date().getFullYear();
}