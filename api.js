function fetchUsers() {
    fetch('test.json')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            document.getElementById('userName').textContent = `Name: ${data.name}`;
            document.getElementById('userAge').textContent = `Age: ${data.age}`;
            
            const profileImage = document.getElementById('userProfile');
            profileImage.src = data.profile;
            profileImage.alt = `${data.name}'s profile picture`;
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            document.getElementById('errorDisplay').innerHTML = 
                `Fetch Failed: ${error.message}`;
        });

    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'test.json', true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                const data = JSON.parse(xhr.responseText);
                console.log('XHR Backup Method Data:', data);
            } catch (parseError) {
                console.error('JSON Parse Error:', parseError);
            }
        } else {
            console.error('XHR Request Failed:', xhr.status);
        }
    };
    xhr.onerror = function() {
        console.error('XHR Network Error');
    };
    xhr.send();
}

fetchUsers();