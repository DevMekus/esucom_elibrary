import Utility from "../core/Utility.js"
import { CONFIG } from "../core/Config.js";

export default class JournalUI {
    constructor(permission = 'student'){        
        this.permission = permission
    } 
    
    journalCard(journal){
        return `
            <div class="col-sm-3">
                <div class="card bounce-card cursor-pointer shadow-sm">
                    <div class="bg-lighst p-3">
                        <h2 class="text-center mt-2">📖</h2>
                    </div>
                    <div class="card-body">
                        <div class="p-2 border-bottom mb-1">
                            <span class="badge bg-success">${Utility.toTitleCase(journal.department_name ?? 'N/a')}</span>
                            <h6 class="card-title" title="${journal.title}">${Utility.truncateText(journal.title, 30)}</h6>
                            <p class="muted small">${Utility.truncateText(journal.url, 30)}</p>
                        </div>
                        <div class="cards-footer">                         
                            <a class="btn btn-sm btn-primary" href="${journal.url}" target="_blank" class="rdl">
                            📖 <span class="hideonMobile">Access</span>
                            </a> 

                        ${this.permission !== 'student' ? `
                            <button class="btn btn-sm btn-light action-btn" data-action="edit" data-id="${journal.id}"><i class="fas fa-pencil"></i></button>

                            <button class="btn btn-sm btn-light action-btn" data-action="delete" data-id="${journal.id}"><i class="fas fa-trash"></i></button>                            
                           
                        ` : ''}                         
                        </div>
                    </div>
                </div>
            </div>            
        `
    }
}