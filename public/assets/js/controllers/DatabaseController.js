import DatabaseService from "../services/DatabaseService.js";
import Utility from "../core/Utility.js";
import DatabaseUI from "../ui/DatabaseUI.js"
import { ApiClient } from "../core/ApiClient.js";

export default class DatabaseController {

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
                                                    
        }; 
        DatabaseService.journals = [];
        DatabaseService.nextCursor = null;
        DatabaseService.prevCursor = null;
        DatabaseService.loading = false;
        await DatabaseService._fetch('next', filters)

        this.cardRows();
    }
    
    static cardRows(){
        const databases =  DatabaseService.database;
        const container = Utility.el('card_row')

        if (!databases || databases.length == 0){
            container.innerHTML = `
                <div class="w-100 d-flex justify-content-center">
                    <div class="card shadow-sm" style="width: 18rem;">
                        <div class="bg-danger p-3">
                            <h2 class="text-center mt-2">📰</h2>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title text-center">NOT FOUND</h5>
                            <p class="card-text text-center">Database(s) not found</p>
                            <p class="muted small text-center">Reset your filter and search again.</p>
                        </div>
                    </div>
                </div>
                `;
            this.updateButtons();
            return
        }
        container.innerHTML = ''
        const dbUI = new DatabaseUI(this.permission)

        const dbUICards = databases.map(database => {
            return  dbUI.databaseCard(database)
        }).join(" ");

        container.innerHTML = dbUICards
        this.updateButtons()
        this.editModal()
    }

    static editModal(){
        document.querySelectorAll('.action-btn')?.forEach(btn => {
            btn.addEventListener('click', async() => {
                const action = btn.dataset.action
                const id = btn.dataset.id

                const filtered = DatabaseService.database.find(d => d.id == id)

                if (action == 'edit'){
                    const name = document.getElementById('name')
                    const url = document.getElementById('url')                  
                    const databaseModal = document.querySelector('.databaseModal')

                    Utility.el('submitBtn').textContent = 'Save Changes'
                    Utility.el('titleDom').textContent = 'Edit'

                    /**add values  */
                    name.value = filtered.name;
                    url.value = filtered.url;
                    
                    databaseModal.id = 'editDatabase';
                    
                    $("#databaseModal").modal("show")

                    databaseModal.addEventListener('submit', async(e) => {
                        e.preventDefault();
                        const data = Utility.toObject(new FormData(e.target))            
                        const result = await Utility.confirm("Update Database?")                        
                        if (!result.isConfirmed){
                            Utility.toast("Action cancelled");
                            return;
                        }                    
                        const isUpdated = await ApiClient(`database/update/${id}`, data, "PATCH")                         
                        Utility.SweetAlertResponse(isUpdated);
                        if (!isUpdated.success){
                            Utility.toast('Update failed. An error occurred')
                            return
                        }                        
                        Utility.reloadPage() 
                    })
                }
                if (action == 'delete'){
                    const result = await Utility.confirm("Delete Database?")                        
                    if (!result.isConfirmed){
                        Utility.toast("Action cancelled");
                        return;
                    }                    
                    const isDeleted = await ApiClient(`database/${id}`, {}, "DELETE")
                    Utility.SweetAlertResponse(isDeleted);
                    if (!isDeleted.success){
                        Utility.toast('Delete failed. An error occurred')
                        return
                    }

                    Utility.toast('Ebook Deleted','success');
                    
                    Utility.reloadPage() 
                }
            })
        })
    }
    
    
    static updateButtons(){
        if (!DatabaseService.hasNext && !DatabaseService.hasPrev){
            document.getElementById('pagination').classList.add('d-none')
            return
        }
        document.getElementById('pagination').classList.remove('d-none')
        this.btnNext.disabled = !DatabaseService.hasNext;
        this.btnPrev.disabled = !DatabaseService.hasPrev;

        /**Btn class */
        !DatabaseService.hasPrev ? this.btnPrev.classList.remove('btn-primary')
                            : this.btnPrev.classList.add('btn-primary')

        !DatabaseService.hasNext ? this.btnNext.classList.remove('btn-primary')
                            : this.btnNext.classList.add('btn-primary')
    }

    static buttonEvents(){
        document.getElementById('nextBtn').addEventListener('click', async() => {           
            await DatabaseService._fetch('next');
            this.cardRows();
        });
        
        document.getElementById('prevBtn').addEventListener('click', async() => {
            await DatabaseService._fetch('prev');
            this.cardRows();
        });

        /**search */
        Utility.el('searchBtn').addEventListener('click', async(e) => {
            await DatabaseController.initializeData();
        })
        

        /**searchInput clear event */

        Utility.el("searchInput").addEventListener('input', async(e)=>{
            if (e.target.value == ''){
                await DatabaseController.initializeData();
            }
        })


         /**Add new database */
        Utility.el("addDatabaseForm").addEventListener('submit', async(e) => {
            e.preventDefault()

            const data = Utility.toObject(new FormData(e.target))
            
            const result = await Utility.confirm("Add new Research Database?")
            
            if (!result.isConfirmed){
                Utility.toast("Action cancelled");
                return;
            }
                        
            const isCreated = await ApiClient('database/new', data, "POST")
            console.log(isCreated)
            Utility.SweetAlertResponse(isCreated)

            if (!isCreated.success){
                Utility.toast('Creation failed. An error occurred')
                return
            }
            
            Utility.reloadPage() 
        })


        Utility.el('newDatabaseBtn').addEventListener('click', (e) => {
            const name = document.getElementById('name').value = ''
            const url = document.getElementById('url').value = ''                  
            const databaseModal = document.querySelector('.databaseModal')

            Utility.el('submitBtn').textContent = 'Save Database'
            Utility.el('titleDom').textContent = 'Add'
        })

       
        

    }
}