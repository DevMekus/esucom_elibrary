import { CONFIG } from "./core/Config.js"
import Utility from "./core/Utility.js"
import EbookController from "./controllers/EbookController.js";
import JournalController from "./controllers/JournalController.js";

const page = document.body.dataset.page;

document.addEventListener('DOMContentLoaded', () => {

    KPIBounceCards()
 
    switch(page){
      
        case 'ebook':
            EbookController.init();
        break;
        case 'journal':
            JournalController.init();
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