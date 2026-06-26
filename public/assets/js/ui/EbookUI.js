import Utility from "../core/Utility.js"
import { CONFIG } from "../core/Config.js";

export default class EbookUI{
    constructor(permission = 'student'){        
        this.permission = permission
    }    

    eBookCard(book){

        const dbPath = book.url;
        const url = new URL(dbPath);
        const filename = url.pathname.split("/").pop();
        const viewerUrl = `${CONFIG.BASE_URL}/public/view-pdf.php?file=${encodeURIComponent(filename)}`;

        return `
            <div class="col-sm-3 mb-1">
                <div class="card bounce-card cursor-pointer shadow-sm" style="width: 18rem;">
                    <div class="bg-accent p-3">
                        <h2 class="text-center mt-2">📖</h2>
                    </div>
                    <div class="card-body">
                       <div class="p-2 border-bottom mb-1">
                            <span class="badge bg-success">${Utility.toTitleCase(book.category ?? 'N/a')}</span>
                            <h6 class="card-title" title="${book.title}">${Utility.truncateText(book.title, 40)}</h6>
                            <p class="muted small" title="${book.author}">${Utility.truncateText(book.author, 40)}</p>
                       </div>
                       <div class="w-100 d-flex justify-content-end align-center">
                        <a class="btn btn-sm btn-primary" href="${viewerUrl}" target="_blank" class="rdl">
                        📖 View PDF
                        </a>                          
                       </div>
                    </div>
                </div>
            </div>
        
        `
    }
}