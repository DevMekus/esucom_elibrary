import Utility from "../core/Utility.js"
import { CONFIG } from "../core/Config.js";

export default class EbookUI{
    constructor(permission = 'student'){        
        this.permission = permission
    }    

    eBookCard(book){

        const dbPath = book.url;
        let viewerUrl = ''

        if(dbPath !== '' && dbPath !== null){
            const url = new URL(dbPath);
            const filename = url.pathname.split("/").pop();
            viewerUrl = `${CONFIG.BASE_URL}/public/view-pdf.php?file=${encodeURIComponent(filename)}`;
        }
       

        return `
            <div class="col-sm-3">
                <div class="card bounce-card cursor-pointer shadow-sm">
                    <div class="bg-light p-3">
                        <h2 class="text-center mt-2">📖</h2>
                    </div>
                    <div class="card-body">
                       <div class="p-2 border-bottom mb-1">
                            <span class="badge bg-success">${Utility.toTitleCase(book.category ?? 'N/a')}</span>
                            <h6 class="card-title" title="${book.title}">${Utility.truncateText(book.title, 30)}</h6>
                            <p class="muted small" title="${book.author}">${Utility.truncateText(book.author, 40)}</p>
                       </div>
                       <div class="cards-footer">                          
                            <a class="btn btn-sm btn-primary" href="${viewerUrl}" target="_blank" class="rdl">
                            📖 <span class="hideonMobile">View PDF</span>
                            </a> 
                        ${this.permission !== 'student' ? `
                            <button class="btn btn-sm btn-light action-btn" data-action="edit" data-id="${book.id}"><i class="fas fa-pencil"></i></button>

                            <button class="btn btn-sm btn-light action-btn" data-action="delete" data-id="${book.id}"><i class="fas fa-trash"></i></button>                            
                        
                    ` : ''}                              
                       </div>
                    </div>
                </div>
            </div>
        
        `
    }
}