const form = document.getElementById('rsvpForm');
const responseBox = document.getElementById('response');
const yesCount = document.getElementById('yesCount');

function loadCount() {
    fetch('count.php')
        .then(res => res.text())
        .then(total => yesCount.innerText = total);
}

loadCount();

form.addEventListener('submit', e => {
    e.preventDefault();

    const data = new FormData(form);

    fetch('submit.php', {
        method: 'POST',
        body: data
    })
    .then(res => res.text())
    .then(msg => {
        form.style.display = 'none';
        responseBox.innerHTML = msg;
        responseBox.classList.remove('hidden');
        loadCount();
    });
});
