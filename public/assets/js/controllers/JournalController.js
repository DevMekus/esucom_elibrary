import JournalUI from "../ui/JournalUI.js";
import JournalService from "../services/JournalService.js";
import Utility from "../core/Utility.js";

export default class JournalController {

    static async init(){
        this.permission = document.body.dataset.permission ?? 'student';
        this.btnNext = document.getElementById('nextBtn');
        this.btnPrev = document.getElementById('prevBtn');
        this.initializeData()
        this.buttonEvents()       
    }

    static async initializeData(){
        const filters = {            
            search: document.querySelector('#searchInput').value ?? null,
            id: document.querySelector('#category_id').value
                                                    
        }; 
        JournalService.journals = [];
        JournalService.nextCursor = null;
        JournalService.prevCursor = null;
        JournalService.loading = false;
        await JournalService._fetch('next', filters)

        this.cardRows();
    }
    
    static cardRows(){
        const journals =  JournalService.journals;
        const container = Utility.el('card_row')

        if (!journals || journals.length == 0){
            container.innerHTML = `
                <div class="w-100 d-flex justify-content-center">
                    <div class="card shadow-sm" style="width: 18rem;">
                        <div class="bg-danger p-3">
                            <h2 class="text-center mt-2">📰</h2>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title text-center">NOT FOUND</h5>
                            <p class="card-text text-center">Journal(s) not found</p>
                            <p class="muted small text-center">Reset your filter and search again.</p>
                        </div>
                    </div>
                </div>
                `;
            this.updateButtons();
            return
        }
        container.innerHTML = ''
        const journalUI = new JournalUI(this.permission)

        const journalCards = journals.map(journal => {
            return  journalUI.journalCard(journal)
        }).join(" ");

        container.innerHTML = journalCards
        this.updateButtons()
    }
    
    static updateButtons(){
        if (!JournalService.hasNext && !JournalService.hasPrev){
            document.getElementById('pagination').classList.add('d-none')
            return
        }
        document.getElementById('pagination').classList.remove('d-none')
        this.btnNext.disabled = !JournalService.hasNext;
        this.btnPrev.disabled = !JournalService.hasPrev;

        /**Btn class */
        !JournalService.hasPrev ? this.btnPrev.classList.remove('btn-primary')
                            : this.btnPrev.classList.add('btn-primary')

        !JournalService.hasNext ? this.btnNext.classList.remove('btn-primary')
                            : this.btnNext.classList.add('btn-primary')
    }

    static buttonEvents(){
        document.getElementById('nextBtn').addEventListener('click', async() => {           
            await JournalService._fetch('next');
            this.cardRows();
        });
        
        document.getElementById('prevBtn').addEventListener('click', async() => {
            await JournalService._fetch('prev');
            this.cardRows();
        });

        /**search */

        Utility.el('searchBtn').addEventListener('click', async(e) => {
            await JournalController.initializeData();
        })

        /**searchInput clear event */

        Utility.el("searchInput").addEventListener('input', async(e)=>{
            if (e.target.value == ''){
                await JournalController.initializeData();
            }
        })

        /**category_id change event */

        Utility.el("category_id").addEventListener('change', async(e)=>{
            await JournalController.initializeData();
        })

        

    }
}