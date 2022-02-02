if (document.readyState == 'loading') {
    document.addEventListener('DOMContentLoaded', ready)
} else {
    input()
}

function input() {
    var container = document.querySelector('#container')

    for (var i = 0; i < 15; i++) {
    var input = document.createElement('input') // create a new element
    input.classList.add('input') // Add .input class to the created element
    input.placeholder = 'OL_I_ID ' + i // set an attribute
    input.id = 'OL_I_ID' + i // set the ID
    container.append(input) // Append the element to a parent element (container in this case)
    }
}