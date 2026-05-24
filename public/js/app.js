let barChart = null;
let lineChart = null;
let pieChart = null;

function buildParams() {
    let judetSelect = document.getElementById("judetSelect");
    let lunaStart = document.getElementById("lunaStart").value;
    let lunaEnd = document.getElementById("lunaEnd").value;

    let judeteSelectate = [];

    for (let i = 0; i < judetSelect.options.length; i++) {
        if (judetSelect.options[i].selected) {
            judeteSelectate.push(judetSelect.options[i].value);
        }
    }

    let params = new URLSearchParams();

    for (let i = 0; i < judeteSelectate.length; i++) {
        params.append("judete[]", judeteSelectate[i]);
    }

    if (lunaStart !== "") {
        params.append("luna_start", lunaStart);
    }

    if (lunaEnd !== "") {
        params.append("luna_end", lunaEnd);
    }

    return params;
}

function loadData() {
    let params = buildParams();

    fetch("../api/getData.php?" + params.toString())
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            afiseazaDate(data);
            deseneazaGrafice(data);
        })
        .catch(function(error) {
            console.log("Eroare la incarcarea datelor:", error);
        });

    loadStats();
}

function loadStats() {
    let params = buildParams();

    fetch("../api/getStats.php?" + params.toString())
        .then(function(response) {
            return response.json();
        })
        .then(function(stats) {
            afiseazaStatistici(stats);
        })
        .catch(function(error) {
            console.log("Eroare la incarcarea statisticilor:", error);
        });
}

function loadInitialData() {
    fetch("../api/getData.php")
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            puneJudeteInSelect(data);
            afiseazaDate(data);
            deseneazaGrafice(data);
            loadStats();
            initHarta();
            loadMapData();
        })
        .catch(function(error) {
            console.log("Eroare la incarcarea datelor:", error);
        });
}

function puneJudeteInSelect(data) {
    let select = document.getElementById("judetSelect");
    let judeteExistente = [];

    for (let i = 0; i < data.length; i++) {
        let judet = data[i].judet;

        if (!judeteExistente.includes(judet)) {
            judeteExistente.push(judet);

            let option = document.createElement("option");
            option.value = judet;
            option.textContent = judet;
            select.appendChild(option);
        }
    }
}

function afiseazaStatistici(stats) {
    document.getElementById("totalSomeri").textContent = stats.total_someri;
    document.getElementById("rataMedie").textContent = stats.rata_medie + "%";

    document.getElementById("rataMaxima").textContent = stats.max_rata + "%";
    document.getElementById("rataMaximaInfo").textContent = stats.max_judet + " - " + stats.max_luna;

    document.getElementById("rataMinima").textContent = stats.min_rata + "%";
    document.getElementById("rataMinimaInfo").textContent = stats.min_judet + " - " + stats.min_luna;
}

function afiseazaDate(data) {
    let body = document.getElementById("dataBody");
    body.textContent = "";

    for (let i = 0; i < data.length; i++) {
        let row = document.createElement("tr");

        let judetCell = document.createElement("td");
        judetCell.textContent = data[i].judet;

        let lunaCell = document.createElement("td");
        lunaCell.textContent = data[i].luna;

        let totalCell = document.createElement("td");
        totalCell.textContent = data[i].numar_total;

        let femeiCell = document.createElement("td");
        femeiCell.textContent = data[i].numar_femei;

        let barbatiCell = document.createElement("td");
        barbatiCell.textContent = data[i].numar_barbati;

        let rataCell = document.createElement("td");
        rataCell.textContent = data[i].rata;

        row.appendChild(judetCell);
        row.appendChild(lunaCell);
        row.appendChild(totalCell);
        row.appendChild(femeiCell);
        row.appendChild(barbatiCell);
        row.appendChild(rataCell);

        body.appendChild(row);
    }
}

function deseneazaGrafice(data) {
    deseneazaBarChart(data);
    deseneazaLineChart(data);
    deseneazaPieChart(data);
}

function getLatestMonth(data) {
    let latest = "";

    for (let i = 0; i < data.length; i++) {
        if (data[i].luna > latest) {
            latest = data[i].luna;
        }
    }

    return latest;
}

function deseneazaBarChart(data) {
    let latestMonth = getLatestMonth(data);
    let filteredData = [];

    for (let i = 0; i < data.length; i++) {
        if (data[i].luna === latestMonth) {
            filteredData.push(data[i]);
        }
    }

    let labels = [];
    let values = [];

    for (let i = 0; i < filteredData.length; i++) {
        labels.push(filteredData[i].judet);
        values.push(parseFloat(filteredData[i].rata));
    }

    if (barChart !== null) {
        barChart.destroy();
    }

    barChart = new Chart(document.getElementById("barChart"), {
        type: "bar",
        data: {
            labels: labels,
            datasets: [{
                label: "Rata somajului (%) - " + latestMonth,
                data: values
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

function deseneazaLineChart(data) {
    let luni = [];
    let valoriPeLuna = {};

    for (let i = 0; i < data.length; i++) {
        let luna = data[i].luna;

        if (!luni.includes(luna)) {
            luni.push(luna);
            valoriPeLuna[luna] = {
                suma: 0,
                count: 0
            };
        }

        valoriPeLuna[luna].suma += parseFloat(data[i].rata);
        valoriPeLuna[luna].count++;
    }

    luni.sort();

    let values = [];

    for (let i = 0; i < luni.length; i++) {
        let luna = luni[i];
        let medie = valoriPeLuna[luna].suma / valoriPeLuna[luna].count;
        values.push(medie.toFixed(2));
    }

    if (lineChart !== null) {
        lineChart.destroy();
    }

    lineChart = new Chart(document.getElementById("lineChart"), {
        type: "line",
        data: {
            labels: luni,
            datasets: [{
                label: "Rata medie a somajului (%)",
                data: values
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

function deseneazaPieChart(data) {
    let totalFemei = 0;
    let totalBarbati = 0;

    for (let i = 0; i < data.length; i++) {
        totalFemei += parseInt(data[i].numar_femei);
        totalBarbati += parseInt(data[i].numar_barbati);
    }

    if (pieChart !== null) {
        pieChart.destroy();
    }

    pieChart = new Chart(document.getElementById("pieChart"), {
        type: "pie",
        data: {
            labels: ["Femei", "Barbati"],
            datasets: [{
                label: "Someri",
                data: [totalFemei, totalBarbati]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

function exportData(format) {
    let params = buildParams();
    params.append("format", format);

    let a = document.createElement("a");
    a.href = "../api/export.php?" + params.toString();
    a.download = "somaj_export." + format;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}

var mediiBarChart   = null;
var mediiPieChart   = null;
var varsteChart     = null;
var educatieChart   = null;

function setTableHeader(coloane) {
    var head = document.getElementById("tableHead");
    var tr = document.createElement("tr");
    for (var i = 0; i < coloane.length; i++) {
        var th = document.createElement("th");
        th.textContent = coloane[i];
        tr.appendChild(th);
    }
    head.innerHTML = "";
    head.appendChild(tr);
}

function afiseazaDateMedii(data) {
    setTableHeader(["Judet", "Luna", "Urban Total", "Urban Femei", "Urban Barbati", "Rural Total", "Rural Femei", "Rural Barbati"]);
    var body = document.getElementById("dataBody");
    body.innerHTML = "";
    for (var i = 0; i < data.length; i++) {
        var r = data[i];
        var tr = document.createElement("tr");
        var vals = [r.judet, r.luna, r.urban_total, r.urban_femei, r.urban_barbati, r.rural_total, r.rural_femei, r.rural_barbati];
        for (var j = 0; j < vals.length; j++) {
            var td = document.createElement("td");
            td.textContent = vals[j];
            tr.appendChild(td);
        }
        body.appendChild(tr);
    }
}

function afiseazaDateVarste(data) {
    setTableHeader(["Judet", "Luna", "Sub 25", "25-29", "30-39", "40-49", "50-55", "Peste 55"]);
    var body = document.getElementById("dataBody");
    body.innerHTML = "";
    for (var i = 0; i < data.length; i++) {
        var r = data[i];
        var tr = document.createElement("tr");
        var vals = [r.judet, r.luna, r.sub_25, r.v_25_29, r.v_30_39, r.v_40_49, r.v_50_55, r.peste_55];
        for (var j = 0; j < vals.length; j++) {
            var td = document.createElement("td");
            td.textContent = vals[j];
            tr.appendChild(td);
        }
        body.appendChild(tr);
    }
}

function afiseazaDateEducatie(data) {
    setTableHeader(["Judet", "Luna", "Fara studii", "Primar", "Gimnazial", "Liceal", "Postliceal", "Profesional", "Universitar"]);
    var body = document.getElementById("dataBody");
    body.innerHTML = "";
    for (var i = 0; i < data.length; i++) {
        var r = data[i];
        var tr = document.createElement("tr");
        var vals = [r.judet, r.luna, r.fara_studii, r.primar, r.gimnazial, r.liceal, r.postliceal, r.profesional, r.universitar];
        for (var j = 0; j < vals.length; j++) {
            var td = document.createElement("td");
            td.textContent = vals[j];
            tr.appendChild(td);
        }
        body.appendChild(tr);
    }
}

function loadMediiData() {
    var params = buildParams();
    fetch("../api/getMediiData.php?" + params.toString())
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.length === 0) { return; }
            afiseazaDateMedii(data);

            var judete = [];
            var urban  = [];
            var rural  = [];
            var totalUrban = 0;
            var totalRural = 0;

            var agregate = {};
            for (var i = 0; i < data.length; i++) {
                var j = data[i].judet;
                if (!agregate[j]) { agregate[j] = {urban: 0, rural: 0}; }
                agregate[j].urban += parseInt(data[i].urban_total);
                agregate[j].rural += parseInt(data[i].rural_total);
            }

            for (var j in agregate) {
                judete.push(j);
                urban.push(agregate[j].urban);
                rural.push(agregate[j].rural);
                totalUrban += agregate[j].urban;
                totalRural += agregate[j].rural;
            }

            if (mediiBarChart) { mediiBarChart.destroy(); }
            mediiBarChart = new Chart(document.getElementById("mediiBarChart"), {
                type: "bar",
                data: {
                    labels: judete,
                    datasets: [
                        {label: "Urban", data: urban, backgroundColor: "rgba(54,162,235,0.7)"},
                        {label: "Rural", data: rural, backgroundColor: "rgba(255,159,64,0.7)"}
                    ]
                },
                options: {responsive: true, maintainAspectRatio: false}
            });

            if (mediiPieChart) { mediiPieChart.destroy(); }
            mediiPieChart = new Chart(document.getElementById("mediiPieChart"), {
                type: "pie",
                data: {
                    labels: ["Urban", "Rural"],
                    datasets: [{data: [totalUrban, totalRural]}]
                },
                options: {responsive: true, maintainAspectRatio: false}
            });
        });
}

function loadVarsteData() {
    var params = buildParams();
    fetch("../api/getVarsteData.php?" + params.toString())
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.length === 0) { return; }
            afiseazaDateVarste(data);

            var totale = {sub_25: 0, v_25_29: 0, v_30_39: 0, v_40_49: 0, v_50_55: 0, peste_55: 0};
            for (var i = 0; i < data.length; i++) {
                totale.sub_25   += parseInt(data[i].sub_25);
                totale.v_25_29  += parseInt(data[i].v_25_29);
                totale.v_30_39  += parseInt(data[i].v_30_39);
                totale.v_40_49  += parseInt(data[i].v_40_49);
                totale.v_50_55  += parseInt(data[i].v_50_55);
                totale.peste_55 += parseInt(data[i].peste_55);
            }

            if (varsteChart) { varsteChart.destroy(); }
            varsteChart = new Chart(document.getElementById("varsteChart"), {
                type: "bar",
                data: {
                    labels: ["Sub 25 ani", "25-29 ani", "30-39 ani", "40-49 ani", "50-55 ani", "Peste 55 ani"],
                    datasets: [{label: "Numar someri", data: [
                        totale.sub_25, totale.v_25_29, totale.v_30_39,
                        totale.v_40_49, totale.v_50_55, totale.peste_55
                    ]}]
                },
                options: {responsive: true, maintainAspectRatio: false}
            });
        });
}

function loadEducatieData() {
    var params = buildParams();
    fetch("../api/getEducatieData.php?" + params.toString())
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.length === 0) { return; }
            afiseazaDateEducatie(data);

            var totale = {fara_studii: 0, primar: 0, gimnazial: 0, liceal: 0, postliceal: 0, profesional: 0, universitar: 0};
            for (var i = 0; i < data.length; i++) {
                totale.fara_studii  += parseInt(data[i].fara_studii);
                totale.primar       += parseInt(data[i].primar);
                totale.gimnazial    += parseInt(data[i].gimnazial);
                totale.liceal       += parseInt(data[i].liceal);
                totale.postliceal   += parseInt(data[i].postliceal);
                totale.profesional  += parseInt(data[i].profesional);
                totale.universitar  += parseInt(data[i].universitar);
            }

            if (educatieChart) { educatieChart.destroy(); }
            educatieChart = new Chart(document.getElementById("educatieChart"), {
                type: "bar",
                data: {
                    labels: ["Fara studii", "Primar", "Gimnazial", "Liceal", "Postliceal", "Profesional", "Universitar"],
                    datasets: [{label: "Numar someri", data: [
                        totale.fara_studii, totale.primar, totale.gimnazial,
                        totale.liceal, totale.postliceal, totale.profesional, totale.universitar
                    ]}]
                },
                options: {responsive: true, maintainAspectRatio: false}
            });
        });
}

var tabBtns = document.querySelectorAll(".tab-btn");
for (var ti = 0; ti < tabBtns.length; ti++) {
    tabBtns[ti].addEventListener("click", function() {
        for (var tj = 0; tj < tabBtns.length; tj++) {
            tabBtns[tj].classList.remove("active");
        }
        this.classList.add("active");

        var tabContents = document.querySelectorAll(".tab-content");
        for (var tj = 0; tj < tabContents.length; tj++) {
            tabContents[tj].style.display = "none";
        }
        document.getElementById("tab-" + this.getAttribute("data-tab")).style.display = "block";

        var tab = this.getAttribute("data-tab");
        if (tab === "rata")      { setTableHeader(["Judet", "Luna", "Total someri", "Femei", "Barbati", "Rata somajului (%)"]); loadData(); }
        if (tab === "medii")     { loadMediiData(); }
        if (tab === "varste")    { loadVarsteData(); }
        if (tab === "educatie")  { loadEducatieData(); }
    });
}

document.getElementById("filterButton").addEventListener("click", function() {
    loadData();
});

var hartaLeaflet = null;
var circleMarkeri = [];

var judeteHarta = [
    {judet: "ALBA",                   lat: 46.07, lng: 23.57},
    {judet: "ARAD",                   lat: 46.17, lng: 21.31},
    {judet: "ARGES",                  lat: 45.05, lng: 24.87},
    {judet: "BACAU",                  lat: 46.57, lng: 26.91},
    {judet: "BIHOR",                  lat: 47.05, lng: 22.01},
    {judet: "BISTRITA NASAUD",        lat: 47.13, lng: 24.50},
    {judet: "BOTOSANI",               lat: 47.74, lng: 26.66},
    {judet: "BRAILA",                 lat: 45.27, lng: 27.96},
    {judet: "BRASOV",                 lat: 45.65, lng: 25.61},
    {judet: "BUZAU",                  lat: 45.15, lng: 26.82},
    {judet: "CALARASI",               lat: 44.20, lng: 27.33},
    {judet: "CARAS-SEVERIN",          lat: 45.11, lng: 22.07},
    {judet: "CLUJ",                   lat: 46.79, lng: 23.60},
    {judet: "CONSTANTA",              lat: 44.18, lng: 28.65},
    {judet: "COVASNA",                lat: 45.85, lng: 26.18},
    {judet: "DAMBOVITA",              lat: 44.93, lng: 25.45},
    {judet: "DOLJ",                   lat: 44.31, lng: 23.79},
    {judet: "GALATI",                 lat: 45.43, lng: 27.96},
    {judet: "GIURGIU",                lat: 43.90, lng: 25.97},
    {judet: "GORJ",                   lat: 44.93, lng: 23.27},
    {judet: "HARGHITA",               lat: 46.36, lng: 25.80},
    {judet: "HUNEDOARA",              lat: 45.72, lng: 22.91},
    {judet: "IALOMITA",               lat: 44.60, lng: 27.39},
    {judet: "IASI",                   lat: 47.16, lng: 27.59},
    {judet: "ILFOV",                  lat: 44.62, lng: 26.12},
    {judet: "MARAMURES",              lat: 47.65, lng: 24.08},
    {judet: "MEHEDINTI",              lat: 44.64, lng: 22.66},
    {judet: "MURES",                  lat: 46.54, lng: 24.56},
    {judet: "NEAMT",                  lat: 46.97, lng: 26.38},
    {judet: "OLT",                    lat: 44.43, lng: 24.37},
    {judet: "PRAHOVA",                lat: 45.07, lng: 25.99},
    {judet: "SALAJ",                  lat: 47.19, lng: 23.06},
    {judet: "SATU MARE",              lat: 47.79, lng: 22.88},
    {judet: "SIBIU",                  lat: 45.80, lng: 24.15},
    {judet: "SUCEAVA",                lat: 47.63, lng: 25.74},
    {judet: "TELEORMAN",              lat: 44.02, lng: 25.00},
    {judet: "TIMIS",                  lat: 45.75, lng: 21.23},
    {judet: "TULCEA",                 lat: 45.19, lng: 29.00},
    {judet: "VASLUI",                 lat: 46.64, lng: 27.73},
    {judet: "VALCEA",                 lat: 45.10, lng: 24.37},
    {judet: "VRANCEA",                lat: 45.70, lng: 26.97},
    {judet: "MUNICIPIUL BUCURESTI",   lat: 44.43, lng: 26.10}
];


function normalizeJudet(s) {
    return s.toUpperCase()
        .replace(/[ĂÂ]/g, "A")
        .replace(/[ȘŞ]/g, "S")
        .replace(/[ȚŢ]/g, "T")
        .replace(/[Î]/g, "I")
        .replace(/\?/g, "S")
        .trim();
}

function getColor(t) {
    var r, g, b;
    if (t < 0.5) {
        var s = t * 2;
        r = Math.round(255 + s * (253 - 255));
        g = Math.round(255 + s * (141 - 255));
        b = Math.round(178 + s * (60 - 178));
    } else {
        var s = (t - 0.5) * 2;
        r = Math.round(253 + s * (189 - 253));
        g = Math.round(141 + s * (0 - 141));
        b = Math.round(60 + s * (38 - 60));
    }
    return "rgb(" + r + "," + g + "," + b + ")";
}

function initHarta() {
    hartaLeaflet = L.map("mapContainer").setView([45.9, 24.9], 7);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution: "&copy; OpenStreetMap contributors"
    }).addTo(hartaLeaflet);

    for (var i = 0; i < judeteHarta.length; i++) {
        var c = judeteHarta[i];
        var circle = L.circleMarker([c.lat, c.lng], {
            radius: 16,
            fillColor: "#cccccc",
            color: "#ffffff",
            weight: 2,
            fillOpacity: 0.85
        });
        circle.bindTooltip(c.judet, {direction: "top", sticky: true});
        circle.addTo(hartaLeaflet);
        circleMarkeri.push({judet: c.judet, marker: circle});
    }
}

function coloreazaHarta(data) {
    var rates = [];
    for (var judet in data) {
        rates.push(data[judet]);
    }
    var minRate = Math.min.apply(null, rates);
    var maxRate = Math.max.apply(null, rates);

    for (var i = 0; i < circleMarkeri.length; i++) {
        var item = circleMarkeri[i];
        var rata = null;

        for (var judet in data) {
            if (normalizeJudet(judet) === normalizeJudet(item.judet)) {
                rata = data[judet];
                break;
            }
        }

        if (rata !== null) {
            var t = (maxRate === minRate) ? 0.5 : (rata - minRate) / (maxRate - minRate);
            item.marker.setStyle({fillColor: getColor(t)});
            item.marker.setTooltipContent(item.judet + ": " + rata + "%");
        }
    }
}

function loadMapData() {
    fetch("../api/getMapData.php")
        .then(function(response) {
            return response.json();
        })
        .then(function(result) {
            document.getElementById("mapLuna").textContent = "Date pentru luna: " + result.luna;
            coloreazaHarta(result.data);
        })
        .catch(function(err) {
            console.log("Eroare harta:", err);
        });
}

function exportSVG() {
    let charts = [
        document.getElementById("barChart"),
        document.getElementById("lineChart"),
        document.getElementById("pieChart")
    ];

    let totalHeight = 0;
    let maxWidth = 0;

    for (let i = 0; i < charts.length; i++) {
        totalHeight += charts[i].height + 10;
        if (charts[i].width > maxWidth) {
            maxWidth = charts[i].width;
        }
    }

    let svgParts = ['<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="' + maxWidth + '" height="' + totalHeight + '">'];

    let yOffset = 0;
    for (let i = 0; i < charts.length; i++) {
        let c = charts[i];
        svgParts.push('<image href="' + c.toDataURL("image/png") + '" x="0" y="' + yOffset + '" width="' + c.width + '" height="' + c.height + '"/>');
        yOffset += c.height + 10;
    }

    svgParts.push("</svg>");

    let blob = new Blob([svgParts.join("\n")], { type: "image/svg+xml" });
    let a = document.createElement("a");
    a.href = URL.createObjectURL(blob);
    a.download = "grafice_somaj.svg";
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(a.href);
}

document.getElementById("exportCsvBtn").addEventListener("click", function() {
    exportData("csv");
});

document.getElementById("exportJsonBtn").addEventListener("click", function() {
    exportData("json");
});

document.getElementById("exportSvgBtn").addEventListener("click", function() {
    exportSVG();
});

document.getElementById("exportPdfBtn").addEventListener("click", function() {
    window.print();
});

loadInitialData();