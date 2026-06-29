import OpacUI from "../ui/OpacUI.js";
import OpacService from "../services/OpacService.js";
import Utility from "../core/Utility.js";


export default class OpacController {

    static async init(){
        this.permission = document.body.dataset.permission ?? 'student';
        this.btnNext = document.getElementById('nextBtn');
        this.btnPrev = document.getElementById('prevBtn');
        this.initializeData()
        this.buttonEvents()       
    }

    static async initializeData(){
        const filters = {            
            search: document.querySelector('#searchInput').value ?? null,
            id: document.querySelector('#category_id').value
                                                    
        }; 
        OpacService.journals = [];
        OpacService.nextCursor = null;
        OpacService.prevCursor = null;
        OpacService.loading = false;
        await OpacService._fetch('next', filters)

        this.catalogueRows();
    }
    
    static catalogueRows(){
        const catalogues =  OpacService.catalogues;
        const container = Utility.el('table_row')        

        if (!catalogues || catalogues.length == 0){
            container.innerHTML = `
                <tr>
                    <td colspan="10" class="text-center text-muted py-4">
                        Opac catalog not found
                    </td>
                </tr>
                `;
            this.updateButtons();
            return
        }
        container.innerHTML = ''
        const opacUI = new OpacUI(this.permission)

        const opacTableRows = catalogues.map(row => {
            return  opacUI.opacTableRow(row)
        }).join(" ");

        container.innerHTML = opacTableRows
        this.updateButtons()
    }
    
    static updateButtons(){
        if (!OpacService.hasNext && !OpacService.hasPrev){
            document.getElementById('pagination').classList.add('d-none')
            return
        }
        document.getElementById('pagination').classList.remove('d-none')
        this.btnNext.disabled = !OpacService.hasNext;
        this.btnPrev.disabled = !OpacService.hasPrev;

        /**Btn class */
        !OpacService.hasPrev ? this.btnPrev.classList.remove('btn-primary')
                            : this.btnPrev.classList.add('btn-primary')

        !OpacService.hasNext ? this.btnNext.classList.remove('btn-primary')
                            : this.btnNext.classList.add('btn-primary')
    }

    static buttonEvents(){
        document.getElementById('nextBtn').addEventListener('click', async() => {           
            await OpacService._fetch('next');
            this.catalogueRows();
        });
        
        document.getElementById('prevBtn').addEventListener('click', async() => {
            await OpacService._fetch('prev');
            this.catalogueRows();
        });

        /**search */

        Utility.el('searchBtn').addEventListener('click', async(e) => {
            await OpacController.initializeData();
        })

        /**searchInput clear event */

        Utility.el("searchInput").addEventListener('input', async(e)=>{
            if (e.target.value == ''){
                await OpacController.initializeData();
            }
        })

        /**category_id change event */

        Utility.el("category_id").addEventListener('change', async(e)=>{
            await OpacController.initializeData();
        })

        

    }
}