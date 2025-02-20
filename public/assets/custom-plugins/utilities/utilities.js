// Define the Utilities object
const Utilities = {
    // Method to serialize form data to parameters
    serializeFormToParams: function (formId) {
        const formData = $(formId).serializeArray();
        const newParams = {};

        formData.forEach(function (field) {
            newParams[field.name] = field.value;
        });

        return newParams;
    },

    // Method to get all query string parameters as an array of objects
    getQueryStrings: function (url) {
        //const queryString = window.location.search; // Get the query string from the URL
        const urlParams = new URLSearchParams(url); // Use URLSearchParams to parse the query string
        const queryParamsArray = [];

        // Convert the URLSearchParams to an array of objects
        urlParams.forEach((value, key) => {
            queryParamsArray.push({ key: key, value: value });
        });

        return queryParamsArray;
    },
};