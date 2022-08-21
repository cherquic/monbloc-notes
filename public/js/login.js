/*const signInButtom = document.getElementById('signIn');
const signUpButtom = document.getElementById('signUp');
const container = document.getElementById('container');

signUpButton.addEventListener('click', () => {
  console.log ("add");
  container.classList.add('right-panel-active');
});

signInButtom.addEventListener('click', () => {
  console.log ("remove");
  container.classList.remove('right-panel-active');
});
*/

function signUpOpen(e) {
  container.classList.add('right-panel-active');
  console.log("droite");
}
  

function signInOpen() {
  container.classList.remove('right-panel-active');
  console.log("gauche");
}

