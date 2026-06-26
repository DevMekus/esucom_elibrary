import Utility from "../core/Utility.js"
import { CONFIG } from "../core/Config.js";

export default class JournalUI {
    constructor(permission = 'student'){        
        this.permission = permission
    } 
    
    journalCard(journal){
        return `
            <div class="col-sm-3 mb-1">
                <div class="card bounce-card cursor-pointer shadow-sm" style="width: 18rem;">
                    <div class="bg-success p-3">
                        <h2 class="text-center mt-2">📖</h2>
                    </div>
                    <div class="card-body">
                        <div class="p-2 border-bottom mb-1">
                            <span class="badge bg-success">${Utility.toTitleCase(journal.department_name ?? 'N/a')}</span>
                            <h6 class="card-title" title="${journal.title}">${Utility.truncateText(journal.title, 40)}</h6>
                            <p class="muted small">${Utility.truncateText(journal.url, 40)}</p>
                        </div>
                        <div class="w-100 d-flex justify-content-end align-center">
                            <a class="btn btn-sm btn-primary" href="${journal.url}" target="_blank" class="rdl">
                            📖 Access
                            </a>                          
                        </div>
                    </div>
                </div>
            </div>            
        `
    }
}