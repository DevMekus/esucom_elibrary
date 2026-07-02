import Utility from "../core/Utility.js"
import { CONFIG } from "../core/Config.js";

export default class CategoryUI{
    constructor(permission = 'student'){        
        this.permission = permission
    }    

    tableRows(row){
        return `
            <tr>
                <th>${row.id}</th>
                <td>${Utility.toTitleCase(row.category)}</td>
                <td>
                    <button class="btn btn-sm btn-default action-btn" data-action="edit" data-id="${row.id}"><i class="fas fa-pencil"></i></button>
                    <button class="btn btn-sm btn-error action-btn" data-action="delete" data-id="${row.id}"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        
        `
    }
}