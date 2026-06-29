import EbookUI from "../ui/EbookUI.js";
import Utility from "../core/Utility.js";
import EbookService from "../services/EbookService.js";
import { ApiClient } from "../core/ApiClient.js";


export default class EbookController {

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
        EbookService.ebooks = [];
        EbookService.nextCursor = null;
        EbookService.prevCursor = null;
        EbookService.loading = false;
        await EbookService._fetch('next', filters)

        this.cardRows();
    }

    static cardRows(){
        const eBooks =  EbookService.ebooks;
        const container = Utility.el('card_row')

        if (!eBooks || eBooks.length == 0){
            container.innerHTML = `
                <div class="w-100 d-flex justify-content-center">
                    <div class="card shadow-sm" style="width: 18rem;">
                        <div class="bg-danger p-3">
                            <h2 class="text-center mt-2">📰</h2>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title text-center">NOT FOUND</h5>
                            <p class="card-text text-center">eBook(s) not found</p>
                            <p class="muted small text-center">Reset your filter and search again.</p>
                        </div>
                    </div>
                </div>
                `;
            this.updateButtons();
            return
        }
        container.innerHTML = ''
        const bookUi = new EbookUI(this.permission)

        const books = eBooks.map(book => {
            return  bookUi.eBookCard(book)
        }).join(" ");

        container.innerHTML = books
        this.updateButtons()
    }

    static updateButtons(){
        if (!EbookService.hasNext && !EbookService.hasPrev){
            document.getElementById('pagination').classList.add('d-none')
            return
        }
        document.getElementById('pagination').classList.remove('d-none')
        this.btnNext.disabled = !EbookService.hasNext;
        this.btnPrev.disabled = !EbookService.hasPrev;

        /**Btn class */
        !EbookService.hasPrev ? this.btnPrev.classList.remove('btn-primary')
                            : this.btnPrev.classList.add('btn-primary')

        !EbookService.hasNext ? this.btnNext.classList.remove('btn-primary')
                            : this.btnNext.classList.add('btn-primary')
    }

    

    static buttonEvents(){
        document.getElementById('nextBtn').addEventListener('click', async() => {           
            await EbookService._fetch('next');
            this.cardRows();
        });
        
        document.getElementById('prevBtn').addEventListener('click', async() => {
            await EbookService._fetch('prev');
            this.cardRows();
        });

        /**search */

        Utility.el('searchBtn').addEventListener('click', async(e) => {
            await EbookController.initializeData();
        })

        /**searchInput clear event */

        Utility.el("searchInput").addEventListener('input', async(e)=>{
            if (e.target.value == ''){
                await EbookController.initializeData();
            }
        })

        /**category_id change event */

        Utility.el("category_id").addEventListener('change', async(e)=>{
            await EbookController.initializeData();
        })

        /**Add new book */
        Utility.el("addEbookForm").addEventListener('submit', async(e) => {
            e.preventDefault()
            const data = new FormData(e.target);
            
            const result = await Utility.confirm("Add new eBook?")
            
            if (!result.isConfirmed){
                Utility.toast("Action cancelled");
                return;
            }

            const isCreated = await EbookService.postNewEbook(data)

            if (!isCreated){
                Utility.toast('Creation failed. An error occurred')
                return
            }
            
            Utility.reloadPage() 
        })

       

    }
}