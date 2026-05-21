function loadData() {
    let judet = document.getElementById("judetSelect").value;
    let lunaStart = document.getElementById("lunaStart").value;
    let lunaEnd = document.getElementById("lunaEnd").value;

    let params = new URLSearchParams();

    if (judet !== "") {
        params.append("judet", judet);
    }

    if (lunaStart !== "") {
        params.append("luna_start", lunaStart);
    }

    if (lunaEnd !== "") {
        params.append("luna_end", lunaEnd);
    }

    fetch("../api/getData.php?" + params.toString())
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            afiseazaDate(data);
        })
        .catch(function(error) {
            console.log("Eroare la incarcarea datelor:", error);
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

document.getElementById("filterButton").addEventListener("click", function() {
    loadData();
});

loadInitialData();