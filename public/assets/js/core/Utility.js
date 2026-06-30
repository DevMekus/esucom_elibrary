import { CONFIG } from "../core/Config.js";

/**
 * Utility Class
 * -----------------------------
 * A collection of helper methods for encryption, storage, formatting, validation,
 * DOM manipulation, UI utilities, and client-side data handling.
 *
 * This class provides reusable static methods to:
 * - Handle cryptographic operations (encryption, decryption, key derivation).
 * - Manage user data (encode, decode, validation).
 * - Support UI feedback (toast notifications, loaders, skeletons, modals).
 * - Format numbers, dates, and text consistently.
 * - Export and print data (CSV, PDF, print views).
 * - Provide pagination and empty state rendering.
 *
 * All methods include strict error handling to prevent application crashes.
 */

export default class Utility {
  // ------------------------------
  // Static Properties
  // ------------------------------
  static CURRENTPAGE = 1;
  static PAGESIZE = 10;
  static role = document.body.dataset.role || null;
  static userid = document.body.dataset.userid || null;
  static ModalTitle = Utility.el("detailModalLabel");
  static ModalBody = Utility.el("detailModalBody");
  static ModalFooter = Utility.el("detailModalButtons");
  static today = document.body.dataset.date ?? new Date().toISOString().slice(0, 10);
  static NODATA = Utility.el("no-data");
  /**
   * Shortcut for getElementById
   * @param {string} id
   * @returns {HTMLElement|null}
   */
  static el(id) {
    try {
      return document.getElementById(id);
    } catch (err) {
      console.error(`Error getting element by ID "${id}":`, err);
      return null;
    }
  }

  // ------------------------------
  // Encoding / Encryption Helpers
  // ------------------------------

  /** Convert string to ArrayBuffer */
  static strToBuffer(str) {
    try {
      return new TextEncoder().encode(str);
    } catch (err) {
      console.error("Error converting string to buffer:", err);
      return null;
    }
  }

  /** Convert ArrayBuffer to Base64 string */
  static bufToBase64(buf) {
    try {
      return btoa(String.fromCharCode(...new Uint8Array(buf)));
    } catch (err) {
      console.error("Error converting buffer to base64:", err);
      return "";
    }
  }

  /** Convert Base64 string to Uint8Array */
  static base64ToBuf(b64) {
    try {
      return Uint8Array.from(atob(b64), (c) => c.charCodeAt(0));
    } catch (err) {
      console.error("Error converting base64 to buffer:", err);
      return new Uint8Array();
    }
  }

  /**
   * Derive AES-GCM key using PBKDF2
   * @param {string} passphrase
   * @param {Uint8Array} salt
   * @returns {Promise<CryptoKey|null>}
   */
  static async deriveKey(passphrase, salt) {
    try {
      const baseKey = await crypto.subtle.importKey(
        "raw",
        Utility.strToBuffer(passphrase),
        { name: "PBKDF2" },
        false,
        ["deriveKey"]
      );

      return await crypto.subtle.deriveKey(
        {
          name: "PBKDF2",
          salt: salt,
          iterations: 100000,
          hash: "SHA-256",
        },
        baseKey,
        { name: "AES-GCM", length: 256 },
        false,
        ["encrypt", "decrypt"]
      );
    } catch (err) {
      console.error("Error deriving key:", err);
      return null;
    }
  }

  /**
   * Encrypt array data and store in sessionStorage
   * @param {string} keyName
   * @param {Array} arrayData
   * @param {string} passphrase
   * @param {Object} meta
   */
  static async encryptAndStoreArray(keyName, arrayData, passphrase, meta = {}) {
    try {
      const salt = crypto.getRandomValues(new Uint8Array(16));
      const iv = crypto.getRandomValues(new Uint8Array(12));
      const aesKey = await Utility.deriveKey(passphrase, salt);

      if (!aesKey) throw new Error("Failed to derive encryption key.");

      const encoded = new TextEncoder().encode(JSON.stringify(arrayData));
      const ciphertext = await crypto.subtle.encrypt(
        { name: "AES-GCM", iv },
        aesKey,
        encoded
      );

      const payload = {
        ct: Utility.bufToBase64(ciphertext),
        iv: Utility.bufToBase64(iv),
        salt: Utility.bufToBase64(salt),
        ...meta,
      };

      sessionStorage.setItem(keyName, JSON.stringify(payload));
    } catch (err) {
      console.error(`Error encrypting and storing array "${keyName}":`, err);
    }
  }

  /**
   * Decrypt stored array from sessionStorage
   * @param {string} keyName
   * @param {string} passphrase
   * @returns {Promise<Array|null>}
   */
  static async decryptAndGetArray(keyName, passphrase) {
    try {
      const stored = sessionStorage.getItem(keyName);
      if (!stored) return null;

      const { ct, iv, salt } = JSON.parse(stored);
      const aesKey = await Utility.deriveKey(
        passphrase,
        Utility.base64ToBuf(salt)
      );

      if (!aesKey) throw new Error("Failed to derive decryption key.");

      const decrypted = await crypto.subtle.decrypt(
        { name: "AES-GCM", iv: Utility.base64ToBuf(iv) },
        aesKey,
        Utility.base64ToBuf(ct)
      );

      return JSON.parse(new TextDecoder().decode(decrypted));
    } catch (err) {
      console.error(`Error decrypting array "${keyName}":`, err);
      return null;
    }
  }

  // ------------------------------
  // DOM / UI Helpers
  // ------------------------------

  /** Run all methods of a class instance except excluded */
  static runClassMethods(instance, excludeMethods = []) {
    try {
      const prototype = Object.getPrototypeOf(instance);
      if (!prototype) throw new Error("Prototype is undefined or null.");

      const methodNames = Object.getOwnPropertyNames(prototype).filter(
        (name) =>
          typeof instance[name] === "function" &&
          name !== "constructor" &&
          !excludeMethods.includes(name)
      );

      methodNames.forEach((name) => {
        try {
          instance[name]();
        } catch (err) {
          console.error(`Error executing method "${name}":`, err);
        }
      });
    } catch (err) {
      console.error("Error running class methods:", err);
    }
  }

  /**
   * Encode and store user data in sessionStorage
   * @param {Object} data
   */
  static encodeUserData(data) {
    try {
      const encodedData = btoa(
        JSON.stringify({
          id: data.userid,
          fullname: data.fullname,
          email: data.email_address,
          role: data.role_id,
        })
      );
      sessionStorage.setItem("user", encodedData);
      return true;
    } catch (err) {
      console.error("Error encoding user data:", err);
      return false;
    }
  }

  /**
   * Generate inline loading spinner HTML
   * @param {string} size
   */
  static inlineLoader(size = "spinner-border-sm") {
    return `
      <div class="spinner-border ${size}" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    `;
  }

  /**
   * Calculate "time ago" from a date string
   * @param {string} dateString
   * @returns {string}
   */
  static timeAgo(dateString) {
    try {
      const isoString = dateString.replace(" ", "T");
      const now = new Date();
      const then = new Date(isoString);

      const seconds = Math.floor((now - then) / 1000);

      const intervals = {
        year: 31536000,
        month: 2592000,
        week: 604800,
        day: 86400,
        hour: 3600,
        minute: 60,
      };

      for (let unit in intervals) {
        const interval = Math.floor(seconds / intervals[unit]);
        if (interval >= 1) {
          return `${interval} ${unit}${interval !== 1 ? "s" : ""} ago`;
        }
      }
      return "just now";
    } catch (err) {
      console.error("Error calculating time ago:", err);
      return "";
    }
  }

  /**
   * Generate a unique ID
   * @param {string} prefix
   */
  static generateId(prefix = "ps") {
    try {
      // always a 5-digit number (between 10000 and 99999)
      const random5 = Math.floor(10000 + Math.random() * 90000);

      return `${prefix}${random5}`;
    } catch (err) {
      console.error("Error generating ID:", err);
      return `${prefix}-00000`;
    }
  }

  /**
   * Truncate text to a max length
   * @param {string} text
   * @param {number} maxLength
   */
  static truncateText(text, maxLength) {
    try {
      if (text.length <= maxLength) return text;
      const truncated = text.substring(0, maxLength);
      return truncated.substring(0, truncated.lastIndexOf(" ")) + "...";
    } catch (err) {
      console.error("Error truncating text:", err);
      return text;
    }
  }

  // ------------------------------
  // Form Helpers
  // ------------------------------

  /** Convert FormData to plain object */
  static toObject(formData) {
    try {
      const obj = {};
      for (const [key, value] of formData.entries()) {
        if (key.endsWith("[]")) {
          const cleanKey = key.slice(0, -2);
          if (!obj[cleanKey]) obj[cleanKey] = [];
          obj[cleanKey].push(value);
        } else {
          obj[key] = value;
        }
      }
      return obj;
    } catch (err) {
      console.error("Error converting FormData to object:", err);
      return {};
    }
  }

  /**
   * Get the last segment of the current URL path.
   * @returns {string} Last path segment
   */
  static pathId() {
    try {
      const path = window.location.pathname;
      const pathParts = path.split("/");
      return pathParts[pathParts.length - 1];
    } catch (error) {
      console.error("Error getting pathId:", error);
      return "";
    }
  }

  /**
   * Adds print functionality to a button for a specific container.
   */
  static printContent() {
    try {
      const printButton = document.querySelector(".printBtn");
      const container = document.querySelector(".print-section");
      if (!printButton || !container) return;

      printButton.addEventListener("click", () => {
        try {
          const printContent = container.innerHTML;
          const win = window.open("", "", "height=800,width=1000");
          win.document.write("<html><head><title>Order Details</title>");
          win.document.write(
            '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">'
          );
          win.document.write("</head><body>");
          win.document.write(printContent);
          win.document.write("</body></html>");
          win.document.close();
          win.focus();
          win.print();
          win.close();
        } catch (err) {
          console.error("Error during print operation:", err);
        }
      });
    } catch (error) {
      console.error("Error initializing printContent:", error);
    }
  }

  /**
   * Export an array of objects to CSV and download.
   * @param {Array} data - Array of objects
   * @param {string} filename - CSV filename
   */
  static exportToCSV(data, filename = "export.csv") {
    try {
      Utility.toast("Creating CSV file....", "info");

      if (!Array.isArray(data) || data.length === 0) {
        Utility.toast("Invalid data provided for CSV export.", "error");
        return;
      }

      const headers = Object.keys(data[0]);
      const csvRows = [];

      csvRows.push(headers.join(",")); // Add header row

      data.forEach((obj) => {
        const row = headers.map((key) => {
          let cell = obj[key] ?? "";
          cell = String(cell).replace(/"/g, '""'); // Escape quotes
          return `"${cell}"`;
        });
        csvRows.push(row.join(","));
      });

      const csvString = csvRows.join("\n");
      const blob = new Blob([csvString], { type: "text/csv;charset=utf-8;" });
      const link = document.createElement("a");

      const url = URL.createObjectURL(blob);
      link.setAttribute("href", url);
      link.setAttribute("download", filename);
      link.style.display = "none";
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);

      Utility.toast("CSV downloaded", "success");
    } catch (error) {
      console.error("Error exporting CSV:", error);
      Utility.toast("Failed to export CSV.", "error");
    }
  }

  /**
   * Download a PDF from a specific DOM element.
   * @param {string} name - File name
   * @param {string} orient - PDF orientation
   */
  static downloadPDF(name = "file", orient = "portrait") {
    try {
      const pdfButton = document.querySelector(".pdfBtn");
      if (!pdfButton) return;

      pdfButton.addEventListener("click", () => {
        try {
          const element = document.querySelector(".download-section");
          const id = pdfButton.getAttribute("data-loading-id");

          const options = {
            margin: 0.5,
            filename: `${name}_${Utility.generateId(5)}.pdf`,
            image: { type: "jpeg", quality: 0.98 },
            html2canvas: { scale: 2 },
            jsPDF: { unit: "in", format: "a4", orientation: orient },
          };
          html2pdf().from(element).set(options).save();

          Utility.stopButtonLoading(id);
        } catch (err) {
          console.error("Error generating PDF:", err);
        }
      });
    } catch (error) {
      console.error("Error initializing PDF download:", error);
    }
  }

  /**
   * Render a skeleton loader inside a container.
   * @param {string} dom - Container DOM ID
   * @param {string} type - Skeleton type (card/list)
   * @param {number} pageSize - Number of skeleton items
   */
  static cardSkelecton(dom, pageSize = 10) {
    try {
      const container = document.getElementById(dom);
      if (!container) return;

      container.innerHTML = "";
      for (let i = 0; i < pageSize; i++) {
        container.innerHTML += `<div class="skeleton-card skeleton"></div>`;
      }
    } catch (error) {
      console.error("Error rendering skeleton:", error);
    }
  }

  /**
   * Convert a string to title case.
   * @param {string} str
   * @returns {string}
   */
  static toTitleCase(str) {
    try {
      return str
        .toLowerCase()
        .split(" ")
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(" ");
    } catch (error) {
      console.error("Error converting to title case:", error);
      return str;
    }
  }

  /**
   * Reloads the page after a configurable timeout.
   */
  static reloadPage() {
    try {
      setTimeout(() => window.location.reload(), CONFIG.TIMEOUT);
    } catch (error) {
      console.error("Error reloading page:", error);
    }
  }

  /**
   * Validate a Vehicle Identification Number (VIN).
   * @param {string} v - VIN string
   * @returns {boolean}
   */

  /**
   * Escape HTML characters to prevent XSS.
   * @param {string} s
   * @returns {string}
   */
  static escapeHtml(s) {
    try {
      return String(s)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;");
    } catch (error) {
      console.error("Error escaping HTML:", error);
      return s;
    }
  }

  /**
   * Format number as Nigerian Naira.
   * @param {number|string} n
   * @returns {string}
   */
  static fmtNGN(n) {
    try {
      return "₦ " + Number(n).toLocaleString();
    } catch (error) {
      console.error("Error formatting NGN:", error);
      return n;
    }
  }

  /**
   * Generate a unique ID with optional prefix.
   * @param {string} prefix
   * @returns {string}
   */
  static uid(prefix = "N") {
    try {
      return prefix + Math.random().toString(36).slice(2, 9).toUpperCase();
    } catch (error) {
      console.error("Error generating UID:", error);
      return prefix + Date.now();
    }
  }

  /**
   * Format number with locale.
   * @param {number|string} n
   * @returns {string}
   */
  static fmt(n) {
    try {
      return Number(n).toLocaleString();
    } catch (error) {
      console.error("Error formatting number:", error);
      return n;
    }
  }

  /**
   * Get a date string offset by delta days.
   * @param {number} delta - Days to add/subtract
   * @returns {string} YYYY-MM-DD
   */
  static dateStep(delta) {
    try {
      const d = new Date();
      d.setDate(d.getDate() + delta);
      return d.toISOString().slice(0, 10);
    } catch (error) {
      console.error("Error computing dateStep:", error);
      return "";
    }
  }

  /**
   * Display a toast notification.
   * @param {string} msg - Message
   * @param {string} type - Type: info, success, error
   * @param {number} ttl - Time to live (ms)
   */
  static toast(msg, type = "info", ttl = 3000) {
    try {
      const toastEl = document.getElementById("liveToast");
      const msgEl = document.getElementById("toastMsg");

      msgEl.innerHTML = "";
      msgEl.innerHTML = Utility.toTitleCase(msg);

      // Pass autohide and delay options
      const toast = new bootstrap.Toast(toastEl, {
        autohide: true,
        delay: ttl,
      });

      toast.show();
    } catch (error) {
      console.error("Error displaying toast:", error);
    }
  }

  /**
   * Calculate number of pages given a dataset and PER_PAGE.
   * @param {Array} REQUESTS
   * @returns {number}
   */
  static pageCount(REQUESTS) {
    try {
      return Math.ceil(REQUESTS.length / Utility.PER_PAGE);
    } catch (error) {
      console.error("Error computing pageCount:", error);
      return 0;
    }
  }

  /**
   * Generate a numeric hash code from a string.
   * @param {string} s
   * @returns {number}
   */
  static hashCode(s) {
    try {
      let h = 0;
      for (let i = 0; i < s.length; i++) {
        h = (h << 5) - h + s.charCodeAt(i);
        h |= 0; // Convert to 32-bit integer
      }
      return h;
    } catch (error) {
      console.error("Error generating hashCode:", error);
      return 0;
    }
  }

  /**
   * Display a confirmation dialog using SweetAlert.
   * @param {string} title
   * @param {string} message
   * @returns {Promise<Object>} Swal response
   */
  static async confirm(title, message = "Do you wish to continue?") {
    try {
      return await Swal.fire({
        title: `${title}`,
        text: `${message}`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d51d28",
        cancelButtonColor: "#cccccc",
        confirmButtonText: "Yes, Continue!",
      });
    } catch (error) {
      console.error("Error showing confirm dialog:", error);
      return { isConfirmed: false };
    }
  }

  static SweetAlertResponse(response) {
    Swal.fire({
      title: response?.success ? "Success!" : "Error",
      text: response?.message ? Utility.toTitleCase(response.message) : "",
      icon: response?.success ? "success" : "error",
      confirmButtonColor: "#d51d28",
    });
  }

  /**
   * Validate email input.
   * @param {HTMLInputElement} emailInput
   * @param {HTMLElement} emailError
   * @returns {boolean}
   */
  static validateEmail(emailInput, emailError) {
    try {
      emailError.textContent = "";
      const v = emailInput.value.trim();
      const ok = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
      emailError.style.display = ok ? "none" : "block";
      if (!ok) emailError.textContent = "Please enter a valid email address.";
      return ok;
    } catch (error) {
      console.error("Error validating email:", error);
      return false;
    }
  }

  /**
   * Validate password input.
   * @param {HTMLInputElement} password
   * @param {HTMLElement} pwError
   * @returns {boolean}
   */
  static validatePassword(password, pwError) {
    try {
      pwError.textContent = "";
      const v = password.value || "";
      const ok = v.length >= 6;
      pwError.style.display = ok ? "none" : "block";
      if (!ok) pwError.textContent = "Password must be at least 6 characters.";
      return ok;
    } catch (error) {
      console.error("Error validating password:", error);
      return false;
    }
  }

  /**
   * Toggle loading state for a button.
   * @param {boolean} on - Whether to enable loading
   * @param {HTMLElement} btnText - The button's text element
   */
  static setLoading(on, btnText) {
    try {
      // Store the original label only once
      if (!UIHelper._originalLabel) {
        UIHelper._originalLabel = btnText.textContent;
      }

      if (on) {
        AuthStatic.btnSpinner.style.display = "inline-block";
        AuthStatic.btnText.textContent = "Please wait...";
        AuthStatic.submitBtn.disabled = true;
      } else {
        AuthStatic.btnSpinner.style.display = "none";
        AuthStatic.btnText.textContent = UIHelper._originalLabel;
        AuthStatic.submitBtn.disabled = false;
      }
    } catch (error) {
      console.error("Error toggling button loading state:", error);
    }
  }

  /**
   * Render a friendly empty state illustration with optional action.
   * @param {HTMLElement} container - DOM element to render empty state in
   * @param {Object} options - Configuration options
   * @param {string} [options.title="Data not available"] - Main title
   * @param {string} [options.subtitle="We couldn’t find anything to show here right now."] - Subtitle
   * @param {string|null} [options.actionText=null] - Text for action button
   * @param {Function|null} [options.onAction=null] - Callback for action button
   */
  static renderEmptyState(
    container,
    {
      title = "Data not available",
      subtitle = "We couldn’t find anything to show here right now.",
      actionText = null,
      onAction = null,
    } = {}
  ) {
    try {
      if (!container) {
        console.warn("renderEmptyState: container not provided.");
        return;
      }

      const wrapper = document.createElement("div");
      wrapper.className = "empty-state";
      wrapper.setAttribute("role", "status");
      wrapper.setAttribute("aria-live", "polite");

      wrapper.innerHTML = `
      <div class="empty-wrapper status success p-4">       
<svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
  <defs>
    <linearGradient id="cheese" x1="0" x2="1" y1="0" y2="1">
      <stop offset="0" stop-color="#fde68a"/>
      <stop offset="1" stop-color="#facc15"/>
    </linearGradient>
    <linearGradient id="crust" x1="0" x2="1">
      <stop offset="0" stop-color="#f6c67a"/>
      <stop offset="1" stop-color="#f59e0b"/>
    </linearGradient>
  </defs>

  <!-- background plate -->
  <circle cx="100" cy="100" r="95" fill="#fff7ed" stroke="#e5e7eb" stroke-width="3"/>

  <!-- pizza slice -->
  <polygon points="100,35 170,155 30,155" fill="url(#cheese)" stroke="#e07a1f" stroke-width="2"/>
  
  <!-- crust edge -->
  <path d="M100 35 q45 10 70 40 l-10 10 q-25 -25 -60 -30 q-35 5 -60 30 l-10 -10 q25 -30 70 -40z"
        fill="url(#crust)" stroke="#d97706" stroke-width="2"/>

  <!-- melted cheese drips -->
  <path d="M75 135 q5 15 15 0 q8 12 20 0 q8 10 18 -6" fill="#fce77b" opacity="0.9"/>

  <!-- pepperoni -->
  <circle cx="100" cy="85" r="11" fill="#dc2626" stroke="#991b1b" stroke-width="1.5"/>
  <circle cx="78" cy="110" r="9" fill="#c53030" stroke="#7f1d1d" stroke-width="1.3"/>
  <circle cx="125" cy="115" r="9" fill="#c53030" stroke="#7f1d1d" stroke-width="1.3"/>
  <circle cx="110" cy="65" r="8" fill="#ef4444" stroke="#991b1b" stroke-width="1.2"/>

  <!-- basil -->
  <path d="M85 75 q-10 -8 -20 -2 q10 8 20 2" fill="#16a34a"/>
  <path d="M120 100 q12 -8 20 -2 q-12 8 -20 2" fill="#16a34a"/>

  <!-- fun badge face -->
  <g transform="translate(100,165)">
    <circle cx="0" cy="0" r="12" fill="#f59e0b" stroke="#d97706" stroke-width="1.2"/>
    <circle cx="-3" cy="-2.5" r="1.6" fill="#111827"/>
    <circle cx="3" cy="-2.5" r="1.6" fill="#111827"/>
    <path d="M-4 2 q4 3 8 0" stroke="#111827" stroke-width="1.1" fill="none" stroke-linecap="round"/>
  </g>
</svg>

<h3>${title}</h3>
<p>${subtitle}</p>
<div class="actions"></div>
</div> 
`;

      // Add action button if provided
      const actions = wrapper.querySelector(".actions");
      if (actionText && typeof onAction === "function") {
        const btn = document.createElement("button");
        btn.className = "btn";
        btn.type = "button";
        btn.textContent = actionText;
        btn.addEventListener("click", onAction);
        actions.appendChild(btn);
      }

      // Clear container and append empty state
      container.innerHTML = "";
      container.appendChild(wrapper);
    } catch (error) {
      console.error("Error rendering empty state:", error);
    }
  }



   static async detectLocation() {
    if (!navigator.geolocation) {
      Utility.toast("Geolocation not supported", "error");
      return null;
    }

    Utility.toast("Fetching location...", "info");
    return new Promise((resolve) => {
      navigator.geolocation.getCurrentPosition(async (pos) => {
        const { latitude, longitude } = pos.coords;

        
        try {
          const res = await fetch(`${CONFIG.API}/geocode`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ lat: latitude, lon: longitude }),
          });
          const data = await res.json();
          if (!res.ok || data.error || !data.success) return resolve(null);
          resolve(data.data);
        } catch {
          resolve(null);
        }
      }, () => resolve(null));
    });

  }

  static formatDateYMD(date) {
    if (!(date instanceof Date) || isNaN(date)) return null;
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");
    return `${year}-${month}-${day}`;
  }

  static displayFormData(formData) {
    console.group("📦 FormData Contents");

    for (const [key, value] of formData.entries()) {
      if (value instanceof File) {
        console.log(`${key}: File -> ${value.name} (${value.size} bytes)`);
      } else {
        console.log(`${key}: ${value}`);
      }
    }

    console.groupEnd();
  }

  static requestNotificationPermission() {
    if ("Notification" in window) {
      Notification.requestPermission().then((permission) => {
        if (permission === "granted") {
          Utility.toast("Notification permission granted");
        } else {
          Utility.toast("Notification permission granted", "error");
        }
      });
    } else {
      Utility.toast("This browser does not support notifications.");
    }
  }

  static showNotification(title, message) {
    if (Notification.permission === "granted") {
      new Notification(title, {
        body: message,
        icon: `${CONFIG.BASE_URL}/assets/images/icons/bell.png`,
      });
    }
    const audio = new Audio(`${CONFIG.BASE_URL}/assets/audio/bell_sound.mp3`);
    audio
      .play()
      .catch((err) =>
        Utility.toast(`Sound blocked until user interacts: ${err}`)
      );
  }

  static enableNotificationAudio() {
    Notification.requestPermission().then((perm) => {
      if (perm === "granted") {
        const audio = new Audio(
          `${CONFIG.BASE_URL}/assets/audio/bell_sound.mp3`
        );
        audio.volume = 0; // play silently just to unlock
        audio.play().then(() => {
          console.log("Audio unlocked!");
          Utility.toast("Notification sound enabled!");
        });
      }
    });
  }

  static alertLoader(message = "Processing...") {
    Swal.fire({
      title: message,
      text: "Please wait",
      confirmButtonColor: "#d51d28",
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      },
    });
  }

  static clearAlertLoader(){
     Swal.close()
  }

  static printReceipt() {
     document.getElementById("printReceipt").addEventListener("click", () => {
      const receiptContent = document.getElementById("receiptBody").innerHTML;
      const w = window.open("", "PRINT", "height=600,width=380");
        //  font-family: 'Courier New', monospace;

      w.document.write(`
      <html>
        <head>
          <title>Receipt</title>
          <style>
            /* Set page to match thermal paper width */
            @page {
              size: 80mm auto; /* use 58mm if your printer is smaller */
              margin: 3mm;
            }

            /* Reset margins and padding */
            body {
              margin: 0;
              padding: 0;           
              font-size: 16px;
              line-height: 1.3;
            }

            .receipt-container {
              width: 100%;
              padding: 8px 10px 8px 10px; /* add a little more left space */
              box-sizing: border-box;
            }

            /* Remove Bootstrap spacing override */
            * {
              box-sizing: border-box;
            }

            /* Optional: compact tables and headers */
            h2, h3, h4, p {
              margin: 2px 0;
              text-align: center;
            }

            table {
              width: 100%;
              border-collapse: collapse;
            }

            td, th {
              padding: 2px 0;
              text-align: left;
              font-size: 16px;
            }

            hr {
              border: 0;
              border-top: 1px dashed #000;
              margin: 4px 0;
            }

            .center {
              text-align: center;
            }

            .small {
              font-size: 13px;
            }

            .receipt-totals {
              width: 300px; /* fixed container width */
              font-family: 'Courier New', monospace;
              font-size: 16px;
              margin-top: 10px;
              box-sizing: border-box;
            }

            .receipt-totals > div {
              display: flex;
              justify-content: space-between; /* label left, amount right */
              margin: 2px 0;
              white-space: nowrap; /* prevent wrapping */
            }

            .receipt-totals .grand {
              font-size: 16px;
              font-weight: bold;
              margin-top: 6px;
            }

            .receipt-totals .small {
              font-size: 14px;
              color: #666;
            }

          </style>
        </head>
        <body>
          <div class="receipt-container">${receiptContent}</div>
          <script>
            window.onload = function() {
              window.print();
              window.close();
            };
          </script>
        </body>
      </html>
    `);

      w.document.close();
    });
  }

   static flyToCartAnimation(productImgSelector, cartIconSelector) {
        const productImg = document.querySelector(productImgSelector);
        const cartIcon = document.querySelector(cartIconSelector);

        if (!productImg || !cartIcon) {
        console.warn("❌ Fly-to-cart elements not found.");
        return;
        }

        const imgRect = productImg.getBoundingClientRect();
        const cartRect = cartIcon.getBoundingClientRect();

        const clone = productImg.cloneNode(true);
        clone.style.position = "fixed";
        clone.style.top = imgRect.top + "px";
        clone.style.left = imgRect.left + "px";
        clone.style.width = imgRect.width + "px";
        clone.style.height = imgRect.height + "px";
        clone.style.zIndex = "9999";
        clone.style.transition = "all 0.8s ease";
        document.body.appendChild(clone);

        requestAnimationFrame(() => {
            clone.style.top = cartRect.top + "px";
            clone.style.left = cartRect.left + "px";
            clone.style.width = "30px";
            clone.style.height = "30px";
            clone.style.opacity = "0.4";
        });

        clone.addEventListener("transitionend", () => {
            clone.remove();
        });
  }


  static formatNumberInputs(className) {
      const inputs = document.querySelectorAll(`.${className}`);

      inputs.forEach(input => {
          input.addEventListener('input', (e) => {
              let value = e.target.value;
              let cursorPos = e.target.selectionStart;

              // count commas before cursor
              let commasBefore = (value.slice(0, cursorPos).match(/,/g) || []).length;

              // strip non-digits
              let raw = value.replace(/\D/g, '');

              // format with commas
              let formatted = raw.replace(/\B(?=(\d{3})+(?!\d))/g, ',');

              e.target.value = formatted;

              // restore cursor
              let commasAfter = (formatted.slice(0, cursorPos).match(/,/g) || []).length;
              let newCursorPos = cursorPos + (commasAfter - commasBefore);

              e.target.setSelectionRange(newCursorPos, newCursorPos);
          });
      });
  }

  static runCounter() {
        const tArea = document.querySelector('.text_area');
        const counter = document.getElementById("charCount");

        tArea.addEventListener("input", () => {
        counter.textContent = `${tArea.value.length} / 100`;
        tArea.value.length <= 50
            ? (counter.style.color = "green")
            : (counter.style.color = "red");
        });
  }

  static displayPreview(){
        const promoBanner = document.querySelector(".image_select");
        const preview = document.getElementById("preview");
        promoBanner.addEventListener("change", function () {
        preview.innerHTML = "";
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
            const img = document.createElement("img");
            img.src = e.target.result;
            img.style.width = "100%";
            img.style.height = "250px";
            img.style.objectFit = "cover";
            preview.appendChild(img);
            };
            reader.readAsDataURL(file);
        }
        });
  }

  static getBranchIdFromSelector(){
    let id = null
    const selected = document.querySelector('input[name="branchId"]:checked');
    if (selected) {
        id = selected.value;
    }

    return id
  }

  static previewFile() {
      const fileInput = document.getElementById("fileInput");
      const preview = document.getElementById("filePreview");
      fileInput.addEventListener("change", function () {
          const file = this.files[0];
          if (!file) return;
          const fileType = file.type;
          const fileName = file.name;
          preview.innerHTML = "";
          if (fileType === "application/pdf") {
              const iframe = document.createElement("iframe");
              iframe.src = URL.createObjectURL(file);
              iframe.width = "100%";
              iframe.height = "400px";
              preview.appendChild(iframe);
          } else if (
              fileType === "application/msword" ||
              fileType === "application/vnd.openxmlformats-officedocument.wordprocessingml.document"
          ) {
              const info = document.createElement("p");
              info.textContent = `Selected Word document: ${fileName}`;
              preview.appendChild(info);
          } else {
              preview.innerHTML = '<p style="color:red;">Unsupported file type</p>';
              fileInput.value = "";
          }
      });
  }
}
