
const ZipcodeHelper = (function () {
    // Load the zip code data
    const zipData = {
        "1010": ["Innere Stadt, Vienna", "Stephansplatz"],
        "1020": ["Leopoldstadt, Vienna", "Prater"],
        "1030": ["Landstraße, Vienna", "Belvedere"],
        "1040": ["Wieden, Vienna", "Karlsplatz"],
        "1050": ["Margareten, Vienna"],
        "1060": ["Mariahilf, Vienna", "Naschmarkt"],
        "1070": ["Neubau, Vienna", "MuseumsQuartier"],
        "1080": ["Josefstadt, Vienna"],
        "1090": ["Alsergrund, Vienna", "Strudlhofstiege"],
        "1100": ["Favoriten, Vienna", "Oberlaa"],
        "1110": ["Simmering, Vienna"],
        "1120": ["Meidling, Vienna"],
        "1130": ["Hietzing, Vienna", "Schönbrunn"],
        "1140": ["Penzing, Vienna"],
        "1150": ["Rudolfsheim-Fünfhaus, Vienna"],
        "1160": ["Ottakring, Vienna"],
        "1170": ["Hernals, Vienna"],
        "1180": ["Währing, Vienna"],
        "1190": ["Döbling, Vienna", "Grinzing"],
        "1200": ["Brigittenau, Vienna"],
        "1210": ["Floridsdorf, Vienna"],
        "1220": ["Donaustadt, Vienna", "Seestadt"],
        "1230": ["Liesing, Vienna"],
        "4020": ["Linz City Center", "Hafenviertel", "Keferfeld"],
        "4030": ["Bindermichl, Linz", "Ebelsberg"],
        "4040": ["Urfahr, Linz", "Pöstlingberg"],
        "5020": ["Salzburg City Center", "Altstadt", "Schallmoos"],
        "5030": ["Maxglan, Salzburg", "Taxham"],
        "5040": ["Parsch, Salzburg"],
        "5400": ["Adneter Riedl", "Au, Hallein", "Bad Dürrnberg", "Burgfried, Hallein", "Gamp", "Gries, Hallein", "Hallein"],
        "6020": ["Innsbruck City Center", "Wilten", "Pradl"],
        "6100": ["Seefeld in Tirol", "Scharnitz"],
        "6230": ["Brixlegg", "Rattenberg"],
        "6330": ["Kufstein"],
        "6460": ["Imst", "Hoch-Imst"],
        "6500": ["Landeck"],
        "6900": ["Bregenz", "Hinteregg"],
        "7000": ["Eisenstadt", "St. Georgen am Leithagebirge"],
        "8010": ["Graz City Center", "Andritz", "Geidorf"],
        "8020": ["Eggenberg, Graz", "Gösting"],
        "8042": ["Mariatrost, Graz"],
        "8055": ["Seiersberg, Graz"],
        "8700": ["Leoben", "Donawitz"],
        "9400": ["Wolfsberg"],
        "9500": ["Villach", "Landskron"],
        "9800": ["Spittal an der Drau"],
        "9900": ["Lienz"]
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
