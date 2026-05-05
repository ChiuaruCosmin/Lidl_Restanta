let toateDatele = [];

function loadData() {
    fetch("../api/getData.php")
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            toateDatele = data;
            puneJudeteInSelect(data);
            afiseazaDate(data);
        });
}

function puneJudeteInSelect(data) {
    let select = document.getElementById("judetSelect");

    if (select.options.length > 1) {
        return;
    }

    for (let i = 0; i < data.length; i++) {
        let option = document.createElement("option");
        option.value = data[i].judet;
        option.textContent = data[i].judet;
        select.appendChild(option);
    }
}

function afiseazaDate(data) {
    let body = document.getElementById("dataBody");
    body.innerHTML = "";

    for (let i = 0; i < data.length; i++) {
        let row = document.createElement("tr");

        row.innerHTML =
            "<td>" + data[i].judet + "</td>" +
            "<td>" + data[i].luna + "</td>" +
            "<td>" + data[i].rata + "</td>";

        body.appendChild(row);
    }
}

document.getElementById("judetSelect").addEventListener("change", function() {
    let judetAles = this.value;

    if (judetAles == "") {
        afiseazaDate(toateDatele);
        return;
    }

    let dateFiltrate = [];

    for (let i = 0; i < toateDatele.length; i++) {
        if (toateDatele[i].judet == judetAles) {
            dateFiltrate.push(toateDatele[i]);
        }
    }

    afiseazaDate(dateFiltrate);
});

loadData();