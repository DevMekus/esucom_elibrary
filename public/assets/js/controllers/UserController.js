import UsersUI from "../ui/UsersUI.js";
import UserService from "../services/UserService.js";
import Utility from "../core/Utility.js";
import { ApiClient } from "../core/ApiClient.js";

export default class UserController {

    static async init(){
        this.permission = document.body.dataset.permission ?? 'student';
        this.btnNext = document.getElementById('nextBtn');
        this.btnPrev = document.getElementById('prevBtn');
        this.initializeData()
        this.buttonEvents()   
        // Utility.previewFile();    
    }

    static async initializeData(){
        const filters = {            
            search: document.querySelector('#searchInput').value ?? null,
            id: document.querySelector('#role_id').value,
            userid:''
                                                    
        }; 
        UserService.users = [];
        UserService.nextCursor = null;
        UserService.prevCursor = null;
        UserService.loading = false;
        await UserService._get('next', filters)

        this.userRows();
    }
    
    static userRows(){
        const users =  UserService.users;
        const container = Utility.el('table_row')

        if (!users || users.length == 0){
            container.innerHTML = `
                <tr>
                    <td colspan="10" class="text-center text-muted py-4">
                        user(s) not found
                    </td>
                </tr>
                `;
            this.updateButtons();
            return
        }
        container.innerHTML = ''
        const userUI = new UsersUI(this.permission)

        const userRows = users.map(row => {
            return  userUI.userRow(row)
        }).join(" ");

        container.innerHTML = userRows
        this.updateButtons()
        this.editModal()
    }

    static editModal(){
        document.querySelectorAll('.action-btn')?.forEach(btn => {
            btn.addEventListener('click', async() => {
                const action = btn.dataset.action
                const id = btn.dataset.id

                const filtered = UserService.users.find(u => u.id == id)

                if (action == 'edit'){
                    const fullname = document.getElementById('fullname')
                    const email_address = document.getElementById('email_address')                  
                    const phone = document.getElementById('phone')                  
                    const address = document.getElementById('address')                  
                    const department = document.getElementById('department')                  
                    const level = document.getElementById('level')                  
                    const role_id = document.getElementById('uRole_id')                  
                    const status = document.getElementById('status')                  
                    const filePreview = document.getElementById('filePreview')                  
                                   
                    filePreview.innerHTML = `<div class="imagePreviewer">
                       <img class="avatar" src="${filtered.avatar ?? 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRfThdfzMPpZRVm6E4Y6-Mj30RjwczAzVM8gT5N8tvJOw&s=10'}" />
                    </div>`
                    
                    
                    const userModal = document.querySelector('.userModal')

                    Utility.el('submitBtn').textContent = 'Save Changes'
                    Utility.el('titleDom').textContent = 'Edit'

                    /**add values  */
                    fullname.value = filtered.fullname;
                    email_address.value = filtered.email_address;
                    phone.value = filtered.phone;
                    address.value = filtered.address;
                    department.value = filtered.department;
                    level.value = filtered.level;
                    role_id.value = filtered.role_id;
                    status.value = filtered.status;
                   
                    userModal.id = 'editUserForm';
                    
                    $("#userModal").modal("show")

                    userModal.addEventListener('submit', async(e) => {
                        e.preventDefault();
                        const data = new FormData(e.target);           
                        const result = await Utility.confirm("Update user?")                        
                        if (!result.isConfirmed){
                            Utility.toast("Action cancelled");
                            return;
                        }                    
                        const isUpdated = await ApiClient(`users/update/${id}`, data, "POST")
                                  
                        Utility.SweetAlertResponse(isUpdated);
                        if (!isUpdated.success){
                            Utility.toast('Update failed. An error occurred')
                            return
                        }                        
                        Utility.reloadPage() 
                    })
                }
                if (action == 'delete'){
                    const result = await Utility.confirm("Delete User?")                        
                    if (!result.isConfirmed){
                        Utility.toast("Action cancelled");
                        return;
                    }                    
                    const isDeleted = await ApiClient(`users/${id}`, {}, "DELETE")
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
        if (!UserService.hasNext && !UserService.hasPrev){
            document.getElementById('pagination').classList.add('d-none')
            return
        }
        document.getElementById('pagination').classList.remove('d-none')
        this.btnNext.disabled = !UserService.hasNext;
        this.btnPrev.disabled = !UserService.hasPrev;

        /**Btn class */
        !UserService.hasPrev ? this.btnPrev.classList.remove('btn-primary')
                            : this.btnPrev.classList.add('btn-primary')

        !UserService.hasNext ? this.btnNext.classList.remove('btn-primary')
                            : this.btnNext.classList.add('btn-primary')
    }

    static buttonEvents(){
        document.getElementById('nextBtn').addEventListener('click', async() => {           
            await UserService._get('next');
            this.userRows();
        });
        
        document.getElementById('prevBtn').addEventListener('click', async() => {
            await UserService._get('prev');
            this.userRows();
        });

        /**search */

        Utility.el('searchBtn').addEventListener('click', async(e) => {
            await UserController.initializeData();
        })

        /**searchInput clear event */

        Utility.el("searchInput").addEventListener('input', async(e)=>{
            if (e.target.value == ''){
                await UserController.initializeData();
            }
        })

        /**category_id change event */

        Utility.el("role_id").addEventListener('change', async(e)=>{
            await UserController.initializeData();
        })

        /**Add new user */
        Utility.el("addUserForm").addEventListener('submit', async(e) => {
            e.preventDefault()

            const data = new FormData(e.target);
            
            const result = await Utility.confirm("Add new User?")
            
            if (!result.isConfirmed){
                Utility.toast("Action cancelled");
                return;
            }
                        
            const isCreated = await ApiClient('users/create/new', data, "POST")
            console.log(isCreated)
            Utility.SweetAlertResponse(isCreated)

            if (!isCreated.success){
                Utility.toast('Creation failed. An error occurred')
                return
            }
            
            Utility.reloadPage() 
        })

        // Utility.el('addOpacBtn').addEventListener('click', (e) => {
        //     const author = document.getElementById('author').value = ''
        //     const title = document.getElementById('title').value = ''                  
        //     const accession_no = document.getElementById('accession_no').value = ''                  
        //     const publisher = document.getElementById('publisher').value = ''                  
        //     const category_id = document.getElementById('catelogCart_id').value = ''                  
        //     const publication_place = document.getElementById('publication_place').value = ''                  
        //     const date_of_publication = document.getElementById('date_of_publication').value = ''                  
        //     const call_number = document.getElementById('call_number').value = ''                  
        //     const serial_number = document.getElementById('serial_number').value = ''                  
        //     const shelve_number = document.getElementById('shelve_number').value = ''                  
        //     const copies = document.getElementById('copies').value = ''          

        //     Utility.el('submitBtn').textContent = 'Save Catalog'
        //     Utility.el('titleDom').textContent = 'Add'
        // })

        

    }
}