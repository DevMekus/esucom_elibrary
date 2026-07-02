import { CONFIG } from "./core/Config.js"
import Utility from "./core/Utility.js"
import EbookController from "./controllers/EbookController.js";
import JournalController from "./controllers/JournalController.js";
import DatabaseController from "./controllers/DatabaseController.js";
import OpacController from "./controllers/OpacController.js";
import AuthController from './controllers/AuthController.js';
import UserController from "./controllers/UserController.js";
import CategoryController from "./controllers/CategoryController.js";
import DepartmentController from "./controllers/DepartmentController.js";

const page = document.body.dataset.page;

document.addEventListener('DOMContentLoaded', () => {

    KPIBounceCards()
    logoutFunction();
 
    switch(page){
      
        case 'ebook':
            EbookController.init();
        break;
        case 'journal':
            JournalController.init();
        break;
        case 'database':
            DatabaseController.init();
        break;
        case 'opac':
            OpacController.init();
        break;
        case 'auth':
            AuthController.init()
        break;
        case 'users':
            UserController.init()
        break;
        case 'categories':
            CategoryController.init()
        break;
        case 'departments':
            DepartmentController.init()
        break;
    }
    
})


function displayFooterYear() {
    const domEl = document.getElementById("year");
    if (domEl) domEl.textContent = new Date().getFullYear();
}

function KPIBounceCards() {
    
    function bounceCard() {
      const cards = document.querySelectorAll(".bounce-card");
      cards.forEach((card, idx) => {
        setTimeout(() => {
          card.classList.add("bounce");

          // remove class after animation ends so it can re-trigger
          card.addEventListener(
            "animationend",
            () => card.classList.remove("bounce"),
            { once: true }
          );
        }, idx * 200); // small stagger effect between cards
      });
    }

    setInterval(bounceCard, 5000);
}

function logoutFunction() {
    const logout = document.querySelector(".logout");
    if (!logout) return;

    AuthController.logout();
}