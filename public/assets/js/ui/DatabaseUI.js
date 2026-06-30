import Utility from "../core/Utility.js"
import { CONFIG } from "../core/Config.js";

export default class DatabaseUI {
    constructor(permission = 'student'){        
        this.permission = permission
    } 
    
    databaseCard(database){
        return `
            <div class="col-sm-3">
                <div class="card shadow-sm bounce-card">
                    <div class="bg-light p-3">
                        <h2 class="text-center mt-2">🗄️</h2>
                    </div>
                    <div class="card-body">
                        <h6 class="card-title" title="${database.name}">${Utility.truncateText(database.name, 30)}</h6>
                        <p class="muted small">${Utility.truncateText(database.url, 30)}</p>
                       
                       <div class="cards-footer">
                            <a class="btn btn-sm btn-primary" href="${database.url}" target="_blank" class="rdl">
                            📖 <span class="hideonMobile">Access</span>
                            </a> 
                        ${this.permission !== 'student' ? `
                            <button class="btn btn-sm btn-light action-btn" data-action="edit" data-id="${database.id}"><i class="fas fa-pencil"></i></button>

                            <button class="btn btn-sm btn-light action-btn" data-action="delete" data-id="${database.id}"><i class="fas fa-trash"></i></button>                            
                           
                        ` : ''}                              
                        </div>
                    </div>
                </div>
            </div>            
        `
    }
}