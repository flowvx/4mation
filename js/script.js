function saveAndGo(event, element) 
{
    event.preventDefault(); // Prevent the default form submission behavior

    const card = element.closest('.offer-card'); // Find the closest card element

    const offerData = {
        title : card.querySelector('h3').innerText,
        type : card.querySelector('span').innerText,
        localisation : card.querySelector('p').innerText,
        duration : card.querySelector('strong').innerText,
        niveau : card.querySelector('em').innerText
    };

    localStorage.setItem('selectedOffer', JSON.stringify(offerData)); // Save the offer data to localStorage

    window.location.href = 'Fromulaire.html'; // Redirect to the offer details page
}

