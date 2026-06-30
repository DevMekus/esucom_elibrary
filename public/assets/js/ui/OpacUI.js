import Utility from "../core/Utility.js"
import { CONFIG } from "../core/Config.js";

export default class OpacUI{
    constructor(permission = 'student'){        
        this.permission = permission
    }    

    opacTableRow(row){

        return `
            <tr>
                <th>
                    ${row.id}
                </th>
                <td>
                    <strong>${row.title}</strong>
                    <div style="font-size:0.68rem">CALL NUMBER: ${row.call_number}</div>
                    <div style="font-size:0.68rem;color:var(--text3); font-family:'DM Mono',monospace">PUBLICATION: ${row.publication_place}, ${row.date_of_publication}</div>
                </td>
                <td>
                    ${row.author}
                </td>
                <td>
                    ${row.accession_no ?? "N/A"}
                </td>
                <td>
                    ${row.category ? row.category.toUpperCase() : 'N/A'}
                </td>
                    <td>
                    ${row.publisher}
                </td>
                    <td>
                    ${row.serial_number}
                </td>
                ${this.permission !== 'student' ? `
                <td>                  
                    <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-light action-btn" data-action="edit" data-id="${row.id}"><i class="fas fa-pencil"></i></button>

                        <button class="btn btn-sm btn-light action-btn" data-action="delete" data-id="${row.id}"><i class="fas fa-trash"></i></button>  
                    </div>
                </td>                          
                    
                ` : ''}     

            </tr>
        
        `
    }
}