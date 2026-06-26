import Utility from "../core/Utility.js";
import { ApiClient } from "../core/ApiClient.js";


export default class EbookService {
    static ebooks = null;
    static loading = false;
    static nextCursor = null;
    static prevCursor = null;
    static hasPrev = false;
    static hasNext = false;
    static filters = null

    static async _fetch(direction = 'next', filters = null){
        
        try {
            if (this.loading) {
                console.warn('Blocked: already loading');
                return;
            }
            this.loading = true;

            if (filters) {
                this.filters = filters;
            } 

            const params = new URLSearchParams()
            
            params.append('direction', direction)
                if (direction === 'next' && this.nextCursor){
                params.append('cursor', this.nextCursor)           
            }
    
            if (direction === 'prev' && this.prevCursor){
                params.append('cursor', this.prevCursor)           
            }   

    
            const { search, id } = this.filters;
            if (search !== 'null') params.append('search', search);
            if (id !== 'null') params.append('id', id);
           

            const url = `/ebook?${params.toString()}`;
            
            const payload = await ApiClient(url) 
            
            if (!payload.data || payload.data.data.length === 0){
                this.ebooks = []  
                this.resetCursors();               
                return;
            }

            const results = payload.data?.data 
           
            if (direction === 'next' || direction === 'prev'){               
                this.ebooks = results;
            }   

            //update cursor
            this.nextCursor = payload.data.next_cursor;
            this.prevCursor = payload.data.prev_cursor;
            this.hasNext = payload.data.has_next;
            this.hasPrev = payload.data.has_prev;

        } catch (error) {
            console.error('Fetch failed:', error);
        } finally {
            this.loading = false;
        }
    }

    static resetCursors(){
        this.nextCursor = null;
        this.prevCursor = null;
        this.hasNext = false;
        this.hasPrev = false;
    }


    
}