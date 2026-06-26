import { CONFIG } from "./Config.js";

/**
 *
 * A utility function to perform API requests using Fetch API
 * with support for GET/POST/PUT/DELETE methods, FormData, JSON payloads,
 * and Authorization via session token.
 *
 * @param {string} url - The API endpoint
 * @param {Object|FormData} data - The request payload (optional)
 * @param {string} method - HTTP method (GET, POST, PUT, DELETE, etc.)
 * @returns {Promise<Object>} - JSON response or error object
 */

export async function ApiClient(url, data = {}, method = "GET") {
  try {
    if (!url) throw new Error("URL is required for DataTransfer.");

    // Retrieve token from session storage
    const token = sessionStorage.getItem(CONFIG.TOKEN_KEY_NAME) || "";

    // Initialize fetch options
    const options = {
      method: method.toUpperCase(),
      headers: {
        Accept: "application/json",
        Authorization: `Bearer ${token}`,
      },
    };

    // Add body for non-GET requests
    if (method.toUpperCase() !== "GET") {
      if (data instanceof FormData) {
        options.body = data; // Browser sets Content-Type automatically for FormData
      } else {
        options.headers["Content-Type"] = "application/json";
        options.body = JSON.stringify(data);
      }
    }

    // Perform fetch request
    const response = await fetch(`${CONFIG.API}/${url}`, options);

    if (!response.ok) {
      let errorMessage = response.statusText;

      try {
        const errorData = await response.json();
        errorMessage =
          errorData.message || errorData.error || JSON.stringify(errorData);
      } catch (err) {
        try {
          errorMessage = await response.text();
        } catch {}
      }

      return {
        status: response.status,
        success: false,
        message: errorMessage,
      };
    }

    // Parse JSON response
    const result = await response.json();
    return result;
  } catch (error) {
    console.error("DataTransfer  Error:", error);
    return { error: error.message || "Request failed" };
  }
}
