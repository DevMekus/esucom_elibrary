import Utility from '../core/Utility.js';
import { ApiClient } from '../core/ApiClient.js';

export default class UserService {

    static users = [];
    static analytics = null;
    static loading = false;
    static nextCursor = null;
    static prevCursor = null;
    static hasPrev = false;
    static hasNext = false;
    static filters = null



    static async _get(direction = 'next', filters = null){
    
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
            
            params.append('direction',direction)
                if (direction === 'next' && this.nextCursor){
                params.append('cursor', this.nextCursor)           
            }
    
            if (direction === 'prev' && this.prevCursor){
                params.append('cursor', this.prevCursor)           
            }   
    
            const { search, userid } = this.filters;
            
            if (search !== 'null') params.append('search', search);         
            if (userid !== 'null') params.append('userid', userid);
        
               
            const payload = await ApiClient(`users?${params.toString()}`) 

            if (Utility.role == 'admin'){
                //set the analytics route too: just use the branch Ids
                // const analytics = `/admin/analytics/users?${params.toString()}`
                // await UserService._analytics(analytics)  
            }            
            
            if (!payload.data || payload.data.data.length === 0){
                this.users = []                
                return;
            }
            //merge the payload with existing. We can call the merge function
            const results = payload.data?.data 
            
            if (direction === 'next' || direction === 'prev'){               
                this.users = results;
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

    static async _analytics(url){
        try {
           
            const payload = await get(url)

            if (payload){
                this.analytics = payload
            }

        } catch (error) {
            console.error('Fetch failed:', error);
        }
    }

    
}