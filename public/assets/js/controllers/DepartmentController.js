import DepartmentUI from "../ui/DepartmentUI.js";
import DepartmentService from "../services/DepartmentService.js";
import Utility from "../core/Utility.js";
import { ApiClient } from "../core/ApiClient.js";

export default class DepartmentController {

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
        DepartmentService.departments = [];
        DepartmentService.nextCursor = null;
        DepartmentService.prevCursor = null;
        DepartmentService.loading = false;
        await DepartmentService._fetch('next', filters)

        this.departmentRows();
    }
    
    static departmentRows(){
        const departments =  DepartmentService.departments;
        const container = Utility.el('table_row')        

        if (!departments || departments.length == 0){
            container.innerHTML = `
                <tr>
                    <td colspan="10" class="text-center text-muted py-4">
                        Department(s) not found
                    </td>
                </tr>
                `;
            this.updateButtons();
            return
        }
        container.innerHTML = ''
        const departmentUI = new DepartmentUI(this.permission)

        const departmentUIRows = departments.map(row => {
            return  departmentUI.tableRows(row)
        }).join(" ");

        container.innerHTML = departmentUIRows
        this.updateButtons()
        this.editModal()
    }

    static editModal(){
        document.querySelectorAll('.action-btn')?.forEach(btn => {
            btn.addEventListener('click', async() => {
                const action = btn.dataset.action
                const id = btn.dataset.id

                const filtered = DepartmentService.departments.find(c => c.id == id)

                if (action == 'edit'){
                    const department_name = document.getElementById('department_name')
                    const addDepartment = document.querySelector('.addDepartment')

                    Utility.el('submitBtn').textContent = 'Save Changes'
                    Utility.el('titleDom').textContent = 'Edit'

                    /**add values  */
                    department_name.value = filtered.department_name;                    
                    
                    addDepartment.id = 'editDepartment';                    
                    $("#addDepartment").modal("show")

                    addDepartment.addEventListener('submit', async(e) => {
                        e.preventDefault();
                        const data = Utility.toObject(new FormData(e.target))            
                        const result = await Utility.confirm("Update Department?")                        
                        if (!result.isConfirmed){
                            Utility.toast("Action cancelled");
                            return;
                        }                    
                        const isUpdated = await ApiClient(`admin/department/${id}`, data, "PATCH")                         
                        Utility.SweetAlertResponse(isUpdated);
                        if (!isUpdated.success){
                            Utility.toast('Update failed. An error occurred')
                            return
                        }  
                        Utility.toast('Department updated','success');                      
                        Utility.reloadPage() 
                    })
                }
                if (action == 'delete'){
                    const result = await Utility.confirm("Delete Department?")                        
                    if (!result.isConfirmed){
                        Utility.toast("Action cancelled");
                        return;
                    }                    
                    const isDeleted = await ApiClient(`admin/department/${id}`, {}, "DELETE")
                    Utility.SweetAlertResponse(isDeleted);
                    if (!isDeleted.success){
                        Utility.toast('Delete failed. An error occurred')
                        return
                    }

                    Utility.toast('Department Deleted','success');
                    
                    Utility.reloadPage() 
                }
            })
        })
    }
    
    static updateButtons(){
        if (!DepartmentService.hasNext && !DepartmentService.hasPrev){
            document.getElementById('pagination').classList.add('d-none')
            return
        }
        document.getElementById('pagination').classList.remove('d-none')
        this.btnNext.disabled = !DepartmentService.hasNext;
        this.btnPrev.disabled = !DepartmentService.hasPrev;

        /**Btn class */
        !DepartmentService.hasPrev ? this.btnPrev.classList.remove('btn-primary')
                            : this.btnPrev.classList.add('btn-primary')

        !DepartmentService.hasNext ? this.btnNext.classList.remove('btn-primary')
                            : this.btnNext.classList.add('btn-primary')
    }

    static buttonEvents(){
        document.getElementById('nextBtn').addEventListener('click', async() => {           
            await DepartmentService._fetch('next');
            this.departmentRows();
        });
        
        document.getElementById('prevBtn').addEventListener('click', async() => {
            await DepartmentService._fetch('prev');
            this.departmentRows();
        });

        /**search */

        Utility.el('searchBtn').addEventListener('click', async(e) => {
            await DepartmentController.initializeData();
        })

        /**searchInput clear event */

        Utility.el("searchInput").addEventListener('input', async(e)=>{
            if (e.target.value == ''){
                await DepartmentController.initializeData();
            }
        })
       

        /**Add new catalogue */
        Utility.el("addDepartmentForm").addEventListener('submit', async(e) => {
            e.preventDefault()

            const data = Utility.toObject(new FormData(e.target))
            
            const result = await Utility.confirm("Add new Department?")
            
            if (!result.isConfirmed){
                Utility.toast("Action cancelled");
                return;
            }
                        
            const isCreated = await ApiClient('admin/department', data, "POST")            
            Utility.SweetAlertResponse(isCreated)

            if (!isCreated.success){
                Utility.toast('Creation failed. An error occurred')
                return
            }
            
            Utility.reloadPage() 
        })

        Utility.el('addDepartmentBtn').addEventListener('click', (e) => {
            document.getElementById('department_name').value = '' 
            Utility.el('submitBtn').textContent = 'Save Department'
            Utility.el('titleDom').textContent = 'Add'
        })

        

    }
}