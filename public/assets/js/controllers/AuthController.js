import UserService from "../services/UserService.js";
import Utility from "../core/Utility.js";
import AuthService from "../services/AuthService.js";
import { CONFIG } from "../core/Config.js";

export default class AuthController {

    static init(){
        const page = document.body.dataset.auth;

        // this.showHidePassword()
        this.pageFeedback()
       
      

        switch (page){
            case 'login':
                this.login()
            break;

            // case 'register':
            //     this.registerAccount()
            // break;

            case 'recover':
                this.recoverAccount()
            break;

            case 'reset':
                this.resetAccountPassword()
            break;
            default:
                Utility.toast("Cannot recognoze command", "error");                
                break;           
        }
    }

    static async redirect(token) {
      
        const decryptToken = jwt_decode(token);
        const { userid, role, roleId } = decryptToken;
        let url = null;
        
       

        const filter = {
            userid: userid,
            search:'',           
        }

        const user = await UserService._get('next', filter);
       
      
        if (!UserService.users){
            //errro
            return
        }
        

        await AuthService.setNewProfile(UserService.users.data[0]);
       
        const intended_url = sessionStorage.getItem('intended_url');

        if (intended_url && roleId == "1") {
            sessionStorage.removeItem('intended_url');
            window.location.href = intended_url;
            return;
        }

        setTimeout(() => {
            window.location.href = `${CONFIG.BASE_URL}/secure/resources/index`;
        }, CONFIG.TIMEOUT);
    }

    static logout() {
      
        const logouts = document.querySelectorAll(".logout");
        if (!logouts) return;

        logouts.forEach(logout => {
            logout.addEventListener("click", async () => {
       
                const result = await Utility.confirm("Logging out?");          
                    if (result.isConfirmed) {
                        const userid = logout.dataset.id;
                        Utility.toast("Please wait...", "info");

                        const response = await AuthService.logout(userid)
                        Utility.SweetAlertResponse(response);
                        response.success && await AuthService.destroyCurrentSession();
                }
            });
        })
    }

    static login(){
        const password = Utility.el("user_password")
        const email = Utility.el("email_address")

        Utility.el("loginForm").addEventListener("submit", async (e) => {

            e.preventDefault()
            
            const ok = AuthService.validatePassword(password)

            if (!ok) {
                Utility.toast("Please fix the errors in the form", "error");
                return;
            }

            const formData = Utility.toObject(new FormData(e.target));
            const isLoggedIn = await AuthService.authenticate(formData, 'auth/login')

            Utility.SweetAlertResponse(isLoggedIn); 
            
            if (!isLoggedIn.success){              
                return
            }   

            

            await AuthService.startNewSession(isLoggedIn.data.token)
            this.redirect(isLoggedIn.data.token)
        })

      
    }

    static recoverAccount(){
        const email = Utility.el("email_address")

        Utility.el("recoverForm").addEventListener("submit", async(e) => {
            e.preventDefault()
            

            const ok = AuthService.validateEmail(email)

            if (!ok) {
                Utility.toast("Please fix the errors in the form", "error");
                return;
            }

            const formData = Utility.toObject(new FormData(e.target));
            const isRecovered = await AuthService.authenticate(formData, 'auth/recover') 

            Utility.SweetAlertResponse(isRecovered);

            if (!isRecovered.success){
                return
            }
        }) 

        
    }

    static resetAccountPassword() {
        const password = Utility.el("new_password")

        let ok = false;

        password.addEventListener("input", () => {
            ok = AuthService.validatePassword(password);
        })

        Utility.el("resetForm").addEventListener("submit", async(e) => {
            e.preventDefault()
            

            if (!ok) {
                Utility.toast("Please fix the errors in the form", "error");
                return;
            }

            const result = await Utility.confirm("Reset password ?")
                        
            if (!result.isConfirmed){
                Utility.toast("Action cancelled");
                return;
            }

            const formData = Utility.toObject(new FormData(e.target));
            const isReset = await AuthService.authenticate(formData, 'auth/reset')
            Utility.SweetAlertResponse(isReset);

            setTimeout(() => {
                window.location.href = `${CONFIG.BASE_URL}/auth/login`;
            }, CONFIG.TIMEOUT);
        })  
    }

    static pageFeedback() {
        try {
            const params = new URLSearchParams(document.location.search);
            const dom = Utility.el("a-info")
            const urlParam = params.get("f-bk");
            if (!urlParam) return;

            if (urlParam === "Expr"){
                Utility.toast("SESSION EXPIRED! Please sign in", "error");
                dom.innerHTML = `<div class="alert alert-danger" role="alert">
                                    Session Expired! Please sign in!
                                </div>`;
            }
            if (urlParam === "UNAUTHORIZED"){
                Utility.toast("UNAUTHORIZED! Please sign in", "error");
                dom.innerHTML = `<div class="alert alert-info" role="alert">
                                    Sign in to continue!
                                </div>`;
            }
            
            if (urlParam === "logout"){
                Utility.toast("Logout successful", "success");
                dom.innerHTML = `<div class="alert alert-success" role="alert">
                                    Logout is successful!
                                </div>`;
            }

            if (urlParam === "new"){
                Utility.toast("Registration Successful", "success");
                dom.innerHTML = `<div class="alert alert-success" role="alert">
                                    Registration Successful. Login to continue!
                                </div>`;
            }
            if (urlParam === "checkout"){
                sessionStorage.setItem(
                    'intended_url',
                    `${CONFIG.BASE_URL}/checkout`
                );
                Utility.toast("Login to continue", "success");
                dom.innerHTML = `
                <div class="alert alert-danger" role="alert">
                    Please Login and continue with Checkout!
                </div>
                `;
            }

            if (urlParam === "delete-account"){
                sessionStorage.setItem(
                    'intended_url',
                    `${CONFIG.BASE_URL}/account_delete`
                );
                Utility.toast("Login to continue", "success");
                Utility.el("loginTitle").textContent = "Sign in and Delete Account";
                dom.innerHTML = `<span class="bold color-red">Please login and Confirm Account Deletion.</span>`;
            }
            
        } catch (error) {
            console.error("Error showing page feedback:", error);
        }
    }
}