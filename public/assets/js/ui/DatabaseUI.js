import Utility from "../core/Utility.js"
import { CONFIG } from "../core/Config.js";

export default class DatabaseUI {
    constructor(permission = 'student'){        
        this.permission = permission
    } 
    
    databaseCard(database){
        return `
            <div class="col-sm-3 mb-1">
                <div class="card shadow-sm bounce-card" style="width: 18rem;">
                    <div class="bg-light p-3">
                        <h2 class="text-center mt-2">🗄️</h2>
                    </div>
                    <div class="card-body">
                        <h6 class="card-title" title="${database.name}">${Utility.truncateText(database.name, 40)}</h6>
                        <p class="muted small">${Utility.truncateText(database.url, 40)}</p>
                       
                       <div class="w-100 d-flex justify-content-end align-center">
                            <a class="btn btn-sm btn-primary" href="${database.url}" target="_blank" class="rdl">
                            📖 Access
                            </a> 
                        ${this.permission == 'admin' ? `
                            <button class="btn btn-sm btn-light" data-action="edit" data-id="${database.id}"><i class="fas fa-pencil"></i></button>

                            <button class="btn btn-sm btn-light" data-action="delete" data-id="${database.id}"><i class="fas fa-trash"></i></button>                            
                           
                        ` : ''}                              
                        </div>
                    </div>
                </div>
            </div>            
        `
    }
}