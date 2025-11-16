// Test JavaScript file
function testFunction() {
    var element = document.querySelector('.test-class');
    if (element) {
        element.addEventListener('click', function(event) {
            console.log('Button clicked');
            event.preventDefault();
        });
    }
}

// Another function
function anotherFunction(param1, param2) {
    var result = param1 + param2;
    return result * 2;
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    testFunction();
    console.log('Script loaded');
});
