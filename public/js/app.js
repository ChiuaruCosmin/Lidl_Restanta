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

document.getElementById("filterButton").addEventListener("click", function() {
    loadData();
});

document.getElementById("exportCsvBtn").addEventListener("click", function() {
    exportData("csv");
});

document.getElementById("exportJsonBtn").addEventListener("click", function() {
    exportData("json");
});

loadInitialData();