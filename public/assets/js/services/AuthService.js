import { CONFIG } from "../core/Config.js";
import Utility from "../core/Utility.js";
import { ApiClient } from "../core/ApiClient.js";

export default class AuthService {

    /**
     * Session.js
     * Handles user session management, app data caching, and encryption utilities.
     *
     * Dependencies:
     * - CONFIG.js
     * - Utility.js
     * - jwt-decode (assumed available globally or imported)
     */

    /**
     * Fetch the encryption key from server for app data encryption
     * @returns {Promise<Object|null>} Encryption key object or null on failure
     */
    static async fetchEncryptionKey() {
        try {
            const response = await fetch(`${CONFIG.BASE_URL}/public/set-session.php`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
            body: JSON.stringify({ action: "config" }),
            });
            return await response.json();
        } catch (error) {
            console.warn("⚠️ Failed to fetch encryption key:", error);
            return null;
        }
    }

    /**
 * Start a new user session (JS + PHP session)
 * @param {string} token - JWT token
 * @returns {Promise<Object|null>} Server response or null on failure
 */
    static async startNewSession(token) {
        try {
            if (!token) throw new Error("Token is required to start a session.");

            const decryptToken = jwt_decode(token);
            const { userid, role, roleId, branchId  } = decryptToken;

            // Store JS session
            sessionStorage.setItem(CONFIG.TOKEN_KEY_NAME, token);
            sessionStorage.setItem("user", JSON.stringify({ role, userid }));

            // Store PHP session
            const response = await fetch(`${CONFIG.BASE_URL}/public/set-session.php`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
            body: JSON.stringify({ action: "set", token, role, userid }),
            });

            return await response.json();
        } catch (error) {
            console.warn("⚠️ Failed to start user session:", error);
            return null;
        }
    }

    /**
    * Destroy the current session (JS + PHP)
    */
    static async  destroyCurrentSession() {
        try {
            const response = await fetch(`${CONFIG.BASE_URL}/public/set-session.php`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
            body: JSON.stringify({ action: "clear" }),
            });

            const data = await response.json();
            if (data.success) {
            sessionStorage.clear();
            window.location.href = `${CONFIG.BASE_URL}/auth/login?f-bk=logout`;
            }
        } catch (error) {
            console.warn("⚠️ Failed to destroy user session:", error);
        }
    }

    /**
     * Clear PHP profile session (partial session)
     * @returns {Promise<Object>} Server response
     */
    static async  clearPHPProfileSession() {
        try {
            const response = await fetch(`${CONFIG.BASE_URL}/public/set-session.php`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
            body: JSON.stringify({ action: "unset-p" }),
            });
            return await response.json();
        } catch (error) {
            console.warn("⚠️ Failed to clear PHP profile session:", error);
            return null;
        }
    }

    static async logout(userid){
        try {
        return await ApiClient(
                `auth/logout/${userid}`,
                { userid },
                "POST"
            );
        } catch (error) {
            
        }
    }

    static async authenticate(formData, route){
        try {
            return  await ApiClient(
                `${route}`,
                formData,
                "POST"
            );

          

        } catch (error) {
            
        }
    }


    static async  setNewProfile(user) {
        try {
            // Store PHP session
            const response = await fetch(`${CONFIG.BASE_URL}/public/set-session.php`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
            body: JSON.stringify({ action: "set-p", user }),
            });

            return await response.json();
        } catch (error) {
            console.warn("⚠️ Failed to start user session:", error);
            return null;
        }
    }

    static validatePassword(password){
        return true
    }

    static validateEmail(email){
        return true
    }
}