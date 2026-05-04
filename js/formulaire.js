window.onload = function() {
    // --- 1. RÉCUPÉRATION DES DONNÉES DE L'OFFRE ---
    const data = JSON.parse(localStorage.getItem('selectedOffer'));

    if (data) {
        document.getElementById('offer-title').innerText = data.title || "Non spécifié";
        document.getElementById('offer-type').innerText = data.type || "Non spécifié";
        document.getElementById('offer-location').innerText = data.localisation || "Non spécifié";
        document.getElementById('offer-duration').innerText = data.duration || "Non spécifié";
        document.getElementById('offer-level').innerText = data.niveau || "Non spécifié";
    }

    // --- 2. GESTION DYNAMIQUE DE LA LETTRE DE MOTIVATION ---
    const radioUpload = document.getElementById('lettre-upload');
    const radioRedigee = document.getElementById('lettre-redigee');
    const inputFichier = document.getElementById('lettre');
    const areaTexte = document.getElementById('lettre-redigee-area');

    // Fonction pour basculer l'affichage
    function toggleLetterInput() {
        if (radioUpload.checked) {
            inputFichier.style.display = "block";
            inputFichier.required = true;
            areaTexte.style.display = "none";
            areaTexte.required = false;
        } else {
            inputFichier.style.display = "none";
            inputFichier.required = false;
            areaTexte.style.display = "block";
            areaTexte.required = true;
        }
    }

    // Écouteurs sur les boutons radio
    radioUpload.addEventListener('change', toggleLetterInput);
    radioRedigee.addEventListener('change', toggleLetterInput);
    
    // Initialisation au chargement
    toggleLetterInput();

    // --- 3. VALIDATION ET SÉCURISATION DU FORMULAIRE ---
    const form = document.querySelector('form');

    form.addEventListener('submit', function(event) {
        let isValid = true;
        let errorMessages = [];

        // --- ANTI-INJECTION / SANITISATION ---
        const sanitize = (str) => {
            const temp = document.createElement('div');
            temp.textContent = str;
            return temp.innerHTML.trim();
        };

        // Récupération et nettoyage des valeurs
        const nom = sanitize(document.getElementById('nom').value);
        const prenom = sanitize(document.getElementById('prenom').value);
        const email = document.getElementById('email').value.trim();
        const tel = document.getElementById('tel').value.trim();

        // VALIDATION EMAIL (Regex)
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            isValid = false;
            errorMessages.push("L'adresse email n'est pas valide.");
        }

        // VALIDATION TÉLÉPHONE (Format simple : 10 chiffres)
        const telRegex = /^(?:(?:\+|00)33|0)\s*[1-9](?:[\s.-]*\d{2}){4}$/;
        if (!telRegex.test(tel)) {
            isValid = false;
            errorMessages.push("Le numéro de téléphone n'est pas valide (ex: 0612345678).");
        }

        // VALIDATION FICHIER PDF (Vérification extension)
        const cvFile = document.getElementById('cv').files[0];
        if (cvFile && cvFile.type !== "application/pdf") {
            isValid = false;
            errorMessages.push("Le CV doit être au format PDF.");
        }

        if (radioUpload.checked) {
            const lettreFile = document.getElementById('lettre').files[0];
            if (lettreFile && lettreFile.type !== "application/pdf") {
                isValid = false;
                errorMessages.push("La lettre de motivation doit être au format PDF.");
            }
        }

        // --- GESTION FINALE ---
        if (!isValid) {
            event.preventDefault(); // On bloque l'envoi
            alert("Erreurs dans le formulaire :\n- " + errorMessages.join("\n- "));
        } else {
            // Ici, tu pourrais ajouter un petit message de succès avant l'envoi réel
            console.log("Données sécurisées prêtes à l'envoi :", { nom, prenom, email });
            alert("Candidature en cours d'envoi pour l'offre : " + data.title);
        }
    });
};