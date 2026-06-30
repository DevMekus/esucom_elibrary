import Utility from "../core/Utility.js"
import { CONFIG } from "../core/Config.js";

export default class UsersUI{
    constructor(permission = 'student'){        
        this.permission = permission
    }    

    userRow(row){
        
        return `
            <tr>
                <th>
                    ${row.userid}
                </th>
                <td>
                    <div class="avatar-con">
                        <img class="avatar" src="${row.avatar ?? 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRfThdfzMPpZRVm6E4Y6-Mj30RjwczAzVM8gT5N8tvJOw&s=10'}" />
                        <div>
                            <strong>${row.fullname}</strong>
                            <div style="font-size:0.68rem">Email: ${row.email_address}</div>
                            <div style="font-size:0.68rem;color:var(--text3); font-family:'DM Mono',monospace"> ${row.phone ?? ''}</div> 
                        </div>
                    </div>                
                </td>
                <td>
                    ${row.department_name ? Utility.toTitleCase(row.department_name) : 'N/a'}
                </td>
                <td>
                    ${row.level ?? "N/a"}
                </td>
                <td>
                    ${row.role ?? 'N/a'}
                </td>
                    <td>
                    ${row.created_at}
                </td>
                <td>
                    <span class="badge bg-${row.status == 'active' ? 'success' : 'danger'}">
                        ${Utility.toTitleCase(row.status)}
                    </span>
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