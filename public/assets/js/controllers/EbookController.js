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
        this.previewFile();
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
        this.editModal()       
    }

    static editModal(){
        document.querySelectorAll('.action-btn')?.forEach(btn => {
           btn.addEventListener('click', async() => {
                const action = btn.dataset.action
                const id = btn.dataset.id

      

                const filtered = EbookService.ebooks.find(book => book.id == id)

                if (action == 'edit'){
                    const title = document.getElementById('title')
                    const author = document.getElementById('author')
                    const category_id = document.getElementById('ebookCat_id')
                    const url = document.getElementById('url')
                    const filePreview = document.getElementById('filePreview')
                    const ebookModal = document.querySelector('.ebookModal')

                    /**add values  */
                    title.value = filtered.title;
                    author.value = filtered.author;
                    category_id.value = filtered.category_id;
                    url.value = filtered.url;
                    filePreview.innerHTML = filtered.url;
                    ebookModal.id = 'editModal';
                    
                    $("#ebookModal").modal("show")

                    ebookModal.addEventListener('submit', async(e) => {
                        e.preventDefault();

                        const data = new FormData(e.target);
            
                        const result = await Utility.confirm("Update eBook?")
                        
                        if (!result.isConfirmed){
                            Utility.toast("Action cancelled");
                            return;
                        }
                    
                        const isUpdated = await ApiClient(`update/ebook/${id}`, data, "POST")

                        console.log(isUpdated)

                        if (!isUpdated.success){
                            Utility.toast('Creation failed. An error occurred')
                            return
                        }

                      
                        
                        Utility.reloadPage() 
                    })
                }
                if (action == 'delete'){
                    const result = await Utility.confirm("Delete eBook?")
                        
                    if (!result.isConfirmed){
                        Utility.toast("Action cancelled");
                        return;
                    }
                    
                    const isDeleted = await ApiClient(`ebook/${id}`, {}, "DELETE")

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
           
            const isCreated = await ApiClient('ebook', data, "POST")

            if (!isCreated.success){
                Utility.toast('Creation failed. An error occurred')
                return
            }
            
            Utility.reloadPage() 
        })

        

        

       

    }

    static previewFile() {
        const fileInput = document.getElementById("fileInput");
        const preview = document.getElementById("filePreview");
        fileInput.addEventListener("change", function () {
            const file = this.files[0];
            if (!file) return;
            const fileType = file.type;
            const fileName = file.name;
            preview.innerHTML = "";
            if (fileType === "application/pdf") {
                const iframe = document.createElement("iframe");
                iframe.src = URL.createObjectURL(file);
                iframe.width = "100%";
                iframe.height = "400px";
                preview.appendChild(iframe);
            } else if (
                fileType === "application/msword" ||
                fileType === "application/vnd.openxmlformats-officedocument.wordprocessingml.document"
            ) {
                const info = document.createElement("p");
                info.textContent = `Selected Word document: ${fileName}`;
                preview.appendChild(info);
            } else {
                preview.innerHTML = '<p style="color:red;">Unsupported file type</p>';
                fileInput.value = "";
            }
        });
    }
}