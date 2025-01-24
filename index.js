
function previous() {
    const widthSlider = document.querySelector('.slider').offsetWidth //Récupère la largeur actuelle du slider
    document.querySelector('.slider-content').scrollLeft -= widthSlider
}

function next() { // Objectif décaler le scroll
    const widthSlider = document.querySelector('.slider').offsetWidth //Récupère la largeur actuelle du slider
    document.querySelector('.slider-content').scrollLeft += widthSlider
}