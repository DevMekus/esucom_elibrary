import OpacUI from "../ui/OpacUI.js";
import OpacService from "../services/OpacService.js";
import Utility from "../core/Utility.js";
import { ApiClient } from "../core/ApiClient.js";

export default class OpacController {

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
        OpacService.journals = [];
        OpacService.nextCursor = null;
        OpacService.prevCursor = null;
        OpacService.loading = false;
        await OpacService._fetch('next', filters)

        this.catalogueRows();
    }
    
    static catalogueRows(){
        const catalogues =  OpacService.catalogues;
        const container = Utility.el('table_row')        

        if (!catalogues || catalogues.length == 0){
            container.innerHTML = `
                <tr>
                    <td colspan="10" class="text-center text-muted py-4">
                        Opac catalog not found
                    </td>
                </tr>
                `;
            this.updateButtons();
            return
        }
        container.innerHTML = ''
        const opacUI = new OpacUI(this.permission)

        const opacTableRows = catalogues.map(row => {
            return  opacUI.opacTableRow(row)
        }).join(" ");

        container.innerHTML = opacTableRows
        this.updateButtons()
        this.editModal()
    }

    static editModal(){
        document.querySelectorAll('.action-btn')?.forEach(btn => {
            btn.addEventListener('click', async() => {
                const action = btn.dataset.action
                const id = btn.dataset.id

                const filtered = OpacService.catalogues.find(c => c.id == id)

                if (action == 'edit'){
                    const author = document.getElementById('author')
                    const title = document.getElementById('title')                  
                    const accession_no = document.getElementById('accession_no')                  
                    const publisher = document.getElementById('publisher')                  
                    const category_id = document.getElementById('catelogCart_id')                  
                    const publication_place = document.getElementById('publication_place')                  
                    const date_of_publication = document.getElementById('date_of_publication')                  
                    const call_number = document.getElementById('call_number')                  
                    const serial_number = document.getElementById('serial_number')                  
                    const shelve_number = document.getElementById('shelve_number')                  
                    const copies = document.getElementById('copies')                   
                    
                    
                    
                    const opacModal = document.querySelector('.opacModal')

                    Utility.el('submitBtn').textContent = 'Save Changes'
                    Utility.el('titleDom').textContent = 'Edit'

                    /**add values  */
                    author.value = filtered.author;
                    title.value = filtered.title;
                    accession_no.value = filtered.accession_no;
                    publisher.value = filtered.publisher;
                    category_id.value = filtered.category_id;
                    publication_place.value = filtered.publication_place;
                    date_of_publication.value = filtered.date_of_publication;
                    call_number.value = filtered.call_number;
                    serial_number.value = filtered.serial_number;
                    shelve_number.value = filtered.shelve_number;
                    copies.value = filtered.copies;
                    
                    opacModal.id = 'editOpac';
                    
                    $("#opacModal").modal("show")

                    opacModal.addEventListener('submit', async(e) => {
                        e.preventDefault();
                        const data = Utility.toObject(new FormData(e.target))            
                        const result = await Utility.confirm("Update Catalogue?")                        
                        if (!result.isConfirmed){
                            Utility.toast("Action cancelled");
                            return;
                        }                    
                        const isUpdated = await ApiClient(`catalog/update/${id}`, data, "PATCH")                         
                        Utility.SweetAlertResponse(isUpdated);
                        if (!isUpdated.success){
                            Utility.toast('Update failed. An error occurred')
                            return
                        }                        
                        Utility.reloadPage() 
                    })
                }
                if (action == 'delete'){
                    const result = await Utility.confirm("Delete Catalogue?")                        
                    if (!result.isConfirmed){
                        Utility.toast("Action cancelled");
                        return;
                    }                    
                    const isDeleted = await ApiClient(`catalog/${id}`, {}, "DELETE")
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
        if (!OpacService.hasNext && !OpacService.hasPrev){
            document.getElementById('pagination').classList.add('d-none')
            return
        }
        document.getElementById('pagination').classList.remove('d-none')
        this.btnNext.disabled = !OpacService.hasNext;
        this.btnPrev.disabled = !OpacService.hasPrev;

        /**Btn class */
        !OpacService.hasPrev ? this.btnPrev.classList.remove('btn-primary')
                            : this.btnPrev.classList.add('btn-primary')

        !OpacService.hasNext ? this.btnNext.classList.remove('btn-primary')
                            : this.btnNext.classList.add('btn-primary')
    }

    static buttonEvents(){
        document.getElementById('nextBtn').addEventListener('click', async() => {           
            await OpacService._fetch('next');
            this.catalogueRows();
        });
        
        document.getElementById('prevBtn').addEventListener('click', async() => {
            await OpacService._fetch('prev');
            this.catalogueRows();
        });

        /**search */

        Utility.el('searchBtn').addEventListener('click', async(e) => {
            await OpacController.initializeData();
        })

        /**searchInput clear event */

        Utility.el("searchInput").addEventListener('input', async(e)=>{
            if (e.target.value == ''){
                await OpacController.initializeData();
            }
        })

        /**category_id change event */

        Utility.el("category_id").addEventListener('change', async(e)=>{
            await OpacController.initializeData();
        })

        /**Add new catalogue */
        Utility.el("addOpacForm").addEventListener('submit', async(e) => {
            e.preventDefault()

            const data = Utility.toObject(new FormData(e.target))
            
            const result = await Utility.confirm("Add new Catalogue?")
            
            if (!result.isConfirmed){
                Utility.toast("Action cancelled");
                return;
            }
                        
            const isCreated = await ApiClient('catalog/new', data, "POST")
            console.log(isCreated)
            Utility.SweetAlertResponse(isCreated)

            if (!isCreated.success){
                Utility.toast('Creation failed. An error occurred')
                return
            }
            
            Utility.reloadPage() 
        })

        Utility.el('addOpacBtn').addEventListener('click', (e) => {
            const author = document.getElementById('author').value = ''
            const title = document.getElementById('title').value = ''                  
            const accession_no = document.getElementById('accession_no').value = ''                  
            const publisher = document.getElementById('publisher').value = ''                  
            const category_id = document.getElementById('catelogCart_id').value = ''                  
            const publication_place = document.getElementById('publication_place').value = ''                  
            const date_of_publication = document.getElementById('date_of_publication').value = ''                  
            const call_number = document.getElementById('call_number').value = ''                  
            const serial_number = document.getElementById('serial_number').value = ''                  
            const shelve_number = document.getElementById('shelve_number').value = ''                  
            const copies = document.getElementById('copies').value = ''          

            Utility.el('submitBtn').textContent = 'Save Catalog'
            Utility.el('titleDom').textContent = 'Add'
        })

        

    }
}