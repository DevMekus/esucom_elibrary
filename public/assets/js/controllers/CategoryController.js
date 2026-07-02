import CategoryUI from "../ui/CategoryUI.js";
import CategoryService from "../services/CategoryService.js";
import Utility from "../core/Utility.js";
import { ApiClient } from "../core/ApiClient.js";

export default class CategoryController {

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
        CategoryService.categories = [];
        CategoryService.nextCursor = null;
        CategoryService.prevCursor = null;
        CategoryService.loading = false;
        await CategoryService._fetch('next', filters)

        this.catalogueRows();
    }
    
    static catalogueRows(){
        const categories =  CategoryService.categories;
        const container = Utility.el('table_row')        

        if (!categories || categories.length == 0){
            container.innerHTML = `
                <tr>
                    <td colspan="10" class="text-center text-muted py-4">
                        categories not found
                    </td>
                </tr>
                `;
            this.updateButtons();
            return
        }
        container.innerHTML = ''
        const categoryUI = new CategoryUI(this.permission)

        const categoryUIRows = categories.map(row => {
            return  categoryUI.tableRows(row)
        }).join(" ");

        container.innerHTML = categoryUIRows
        this.updateButtons()
        this.editModal()
    }

    static editModal(){
        document.querySelectorAll('.action-btn')?.forEach(btn => {
            btn.addEventListener('click', async() => {
                const action = btn.dataset.action
                const id = btn.dataset.id

                const filtered = CategoryService.categories.find(c => c.id == id)

                if (action == 'edit'){
                    const category = document.getElementById('category')
                    const addCategory = document.querySelector('.addCategory')

                    Utility.el('submitBtn').textContent = 'Save Changes'
                    Utility.el('titleDom').textContent = 'Edit'

                    /**add values  */
                    category.value = filtered.category;                    
                    
                    addCategory.id = 'editCategory';                    
                    $("#addCategory").modal("show")

                    addCategory.addEventListener('submit', async(e) => {
                        e.preventDefault();
                        const data = Utility.toObject(new FormData(e.target))            
                        const result = await Utility.confirm("Update Category?")                        
                        if (!result.isConfirmed){
                            Utility.toast("Action cancelled");
                            return;
                        }                    
                        const isUpdated = await ApiClient(`admin/category/${id}`, data, "PATCH")                         
                        Utility.SweetAlertResponse(isUpdated);
                        if (!isUpdated.success){
                            Utility.toast('Update failed. An error occurred')
                            return
                        }  
                        Utility.toast('Category updated','success');                      
                        Utility.reloadPage() 
                    })
                }
                if (action == 'delete'){
                    const result = await Utility.confirm("Delete Category?")                        
                    if (!result.isConfirmed){
                        Utility.toast("Action cancelled");
                        return;
                    }                    
                    const isDeleted = await ApiClient(`admin/category/${id}`, {}, "DELETE")
                    Utility.SweetAlertResponse(isDeleted);
                    if (!isDeleted.success){
                        Utility.toast('Delete failed. An error occurred')
                        return
                    }

                    Utility.toast('Category Deleted','success');
                    
                    Utility.reloadPage() 
                }
            })
        })
    }
    
    static updateButtons(){
        if (!CategoryService.hasNext && !CategoryService.hasPrev){
            document.getElementById('pagination').classList.add('d-none')
            return
        }
        document.getElementById('pagination').classList.remove('d-none')
        this.btnNext.disabled = !CategoryService.hasNext;
        this.btnPrev.disabled = !CategoryService.hasPrev;

        /**Btn class */
        !CategoryService.hasPrev ? this.btnPrev.classList.remove('btn-primary')
                            : this.btnPrev.classList.add('btn-primary')

        !CategoryService.hasNext ? this.btnNext.classList.remove('btn-primary')
                            : this.btnNext.classList.add('btn-primary')
    }

    static buttonEvents(){
        document.getElementById('nextBtn').addEventListener('click', async() => {           
            await CategoryService._fetch('next');
            this.catalogueRows();
        });
        
        document.getElementById('prevBtn').addEventListener('click', async() => {
            await CategoryService._fetch('prev');
            this.catalogueRows();
        });

        /**search */

        Utility.el('searchBtn').addEventListener('click', async(e) => {
            await CategoryController.initializeData();
        })

        /**searchInput clear event */

        Utility.el("searchInput").addEventListener('input', async(e)=>{
            if (e.target.value == ''){
                await CategoryController.initializeData();
            }
        })
       

        /**Add new catalogue */
        Utility.el("addCategoryForm").addEventListener('submit', async(e) => {
            e.preventDefault()

            const data = Utility.toObject(new FormData(e.target))
            
            const result = await Utility.confirm("Add new Category?")
            
            if (!result.isConfirmed){
                Utility.toast("Action cancelled");
                return;
            }
                        
            const isCreated = await ApiClient('admin/category', data, "POST")
            console.log(isCreated)
            Utility.SweetAlertResponse(isCreated)

            if (!isCreated.success){
                Utility.toast('Creation failed. An error occurred')
                return
            }
            
            Utility.reloadPage() 
        })

        Utility.el('addCategoryBtn').addEventListener('click', (e) => {
            document.getElementById('category').value = '' 
            Utility.el('submitBtn').textContent = 'Save Category'
            Utility.el('titleDom').textContent = 'Add'
        })

        

    }
}