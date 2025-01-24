
const ZipcodeHelper = (function () {
    // Load the zip code data
    const zipData = {
        "5400": ["Adneter Riedl", "Au, Hallein", "Bad Dürrnberg", "Burgfried, Hallein", "Gamp", "Gries, Hallein", "Hallein"],
        "1010": ["Vienna Center", "Stephansplatz"],
        "4020": ["Linz City Center", "Hafenviertel"]
    };

    return {
        // Validate if the zip code exists
        validateZip: function (zipCode) {
            return zipData.hasOwnProperty(zipCode);
        },

        // Get places for a valid zip code
        getPlacesForZip: function (zipCode) {
            if (this.validateZip(zipCode)) {
                return zipData[zipCode];
            } else {
                return [];
            }
        }
    };
})();
