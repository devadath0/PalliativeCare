document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const symptomInput = document.getElementById('symptomInput');
    const addSymptomBtn = document.getElementById('addSymptomBtn');
    const selectedSymptomsContainer = document.getElementById('selectedSymptoms');
    const emptyMessage = selectedSymptomsContainer.querySelector('.empty-symptoms-message');
    const searchForm = document.getElementById('symptomSearchForm');
    const searchBtn = document.getElementById('searchBtn');
    const loadingResults = document.getElementById('loadingResults');
    const searchResults = document.getElementById('searchResults');
    const errorMessage = document.getElementById('errorMessage');
    const diseasesList = document.getElementById('diseasesList');
    const doctorsList = document.getElementById('doctorsList');
    
    // Array to store selected symptoms
    let selectedSymptoms = [];
    
    // Add event listener for symptom input
    symptomInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addSymptom();
        }
    });
    
    // Add event listener for add symptom button
    addSymptomBtn.addEventListener('click', addSymptom);
    
    // Add event listener for form submission
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        searchSymptoms();
    });
    
    // Function to add a symptom
    function addSymptom() {
        const symptom = symptomInput.value.trim();
        
        if (symptom === '') {
            return;
        }
        
        // Check if symptom already exists
        if (selectedSymptoms.includes(symptom)) {
            alert('This symptom is already added.');
            symptomInput.value = '';
            return;
        }
        
        // Add to array
        selectedSymptoms.push(symptom);
        
        // Hide empty message if this is the first symptom
        if (selectedSymptoms.length === 1 && emptyMessage) {
            emptyMessage.style.display = 'none';
        }
        
        // Create symptom tag
        const symptomTag = document.createElement('div');
        symptomTag.className = 'symptom-tag';
        symptomTag.innerHTML = `
            <span>${symptom}</span>
            <button type="button" class="remove-symptom" data-symptom="${symptom}">&times;</button>
        `;
        
        // Add styles to the symptom tag
        symptomTag.style.backgroundColor = '#e9ecef';
        symptomTag.style.borderRadius = '4px';
        symptomTag.style.padding = '5px 10px';
        symptomTag.style.margin = '0 5px 5px 0';
        symptomTag.style.display = 'inline-flex';
        symptomTag.style.alignItems = 'center';
        
        // Add styles to the remove button
        const removeBtn = symptomTag.querySelector('.remove-symptom');
        removeBtn.style.backgroundColor = 'transparent';
        removeBtn.style.border = 'none';
        removeBtn.style.color = '#6c757d';
        removeBtn.style.marginLeft = '5px';
        removeBtn.style.cursor = 'pointer';
        removeBtn.style.fontWeight = 'bold';
        
        // Add event listener to remove button
        removeBtn.addEventListener('click', function() {
            const symptomToRemove = this.getAttribute('data-symptom');
            removeSymptom(symptomToRemove, symptomTag);
        });
        
        // Add to container
        selectedSymptomsContainer.appendChild(symptomTag);
        
        // Clear input
        symptomInput.value = '';
        
        // Enable search button if we have at least one symptom
        searchBtn.disabled = false;
    }
    
    // Function to remove a symptom
    function removeSymptom(symptom, tagElement) {
        // Remove from array
        const index = selectedSymptoms.indexOf(symptom);
        if (index !== -1) {
            selectedSymptoms.splice(index, 1);
        }
        
        // Remove tag from DOM
        tagElement.remove();
        
        // Show empty message if no symptoms left
        if (selectedSymptoms.length === 0 && emptyMessage) {
            emptyMessage.style.display = 'block';
            searchBtn.disabled = true;
        }
    }
    
    // Function to search symptoms
    function searchSymptoms() {
        if (selectedSymptoms.length === 0) {
            return;
        }
        
        // Show loading spinner
        loadingResults.style.display = 'block';
        searchResults.style.display = 'none';
        errorMessage.style.display = 'none';
        
        // Prepare data for API request
        const data = {
            symptoms: selectedSymptoms
        };
        
        console.log('Sending request to API with data:', data);
        
        // Send API request
        fetch('/palliative/modules/patient/process_symptom_search.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            console.log('Response status:', response.status);
            
            // Clone the response so we can look at the raw text if needed
            const clonedResponse = response.clone();
            
            // Check if response is OK
            if (!response.ok) {
                // Handle 401 Unauthorized specifically
                if (response.status === 401) {
                    window.location.href = '/palliative/index.php?module=auth&action=login&redirect=' + encodeURIComponent(window.location.href);
                    throw new Error('You need to be logged in to use this feature.');
                }
                
                // Try to get the response text to see what's wrong
                return clonedResponse.text().then(text => {
                    console.error('Error response text:', text);
                    throw new Error(`HTTP error! Status: ${response.status}, Response: ${text.substring(0, 200)}...`);
                });
            }
            
            // Try to parse as JSON
            return response.json().catch(err => {
                // If JSON parsing fails, get the raw text
                return clonedResponse.text().then(text => {
                    console.error('Failed to parse JSON. Raw response:', text);
                    throw new Error('Invalid JSON response from server');
                });
            });
        })
        .then(data => {
            console.log('Received data from API:', data);
            
            // Hide loading spinner
            loadingResults.style.display = 'none';
            
            // Show results
            searchResults.style.display = 'block';
            
            // Check for error
            if (data.error) {
                errorMessage.textContent = data.error;
                errorMessage.style.display = 'block';
                return;
            }
            
            // Validate data structure
            if (!data.diseases || !Array.isArray(data.diseases) || !data.recommended_doctors || !Array.isArray(data.recommended_doctors)) {
                console.error('Invalid data structure received:', data);
                errorMessage.textContent = 'The server returned an invalid response format. Please try again later.';
                errorMessage.style.display = 'block';
                return;
            }
            
            // Display diseases
            displayDiseases(data.diseases);
            
            // Display doctors
            displayDoctors(data.recommended_doctors);
        })
        .catch(error => {
            console.error('Error in fetch operation:', error);
            
            // Try the test endpoint as a fallback
            if (error.message.includes('Failed to fetch') || error.message.includes('No such file or directory')) {
                console.log('Trying test endpoint as fallback...');
                
                // Show a message to the user
                errorMessage.textContent = 'Trying alternative method...';
                errorMessage.style.display = 'block';
                
                // Try the test endpoint
                fetch('/palliative/modules/patient/test_symptom_search_v2.php')
                    .then(response => response.json())
                    .then(data => {
                        console.log('Received data from test API:', data);
                        
                        // Hide loading spinner
                        loadingResults.style.display = 'none';
                        
                        // Show results
                        searchResults.style.display = 'block';
                        
                        if (data.status === 'error') {
                            errorMessage.textContent = 'Error: ' + data.message;
                            errorMessage.style.display = 'block';
                            return;
                        }
                        
                        // Display diseases
                        displayDiseases(data.diseases_found);
                        
                        // Display doctors
                        displayDoctors(data.recommended_doctors);
                    })
                    .catch(testError => {
                        console.error('Error in test endpoint:', testError);
                        
                        // Hide loading spinner
                        loadingResults.style.display = 'none';
                        
                        // Show results container
                        searchResults.style.display = 'block';
                        
                        // Show error message
                        errorMessage.textContent = 'All attempts to search symptoms failed. Please try again later.';
                        errorMessage.style.display = 'block';
                    });
                
                return;
            }
            
            // Hide loading spinner
            loadingResults.style.display = 'none';
            
            // Show results container
            searchResults.style.display = 'block';
            
            // Show error message
            errorMessage.textContent = 'An error occurred while processing your request: ' + error.message;
            errorMessage.style.display = 'block';
        });
    }
    
    // Function to display diseases
    function displayDiseases(diseases) {
        diseasesList.innerHTML = '';
        
        if (!diseases || diseases.length === 0) {
            diseasesList.innerHTML = '<div class="alert alert-info">No conditions found matching your symptoms.</div>';
            return;
        }
        
        diseases.forEach(disease => {
            const diseaseItem = document.createElement('div');
            diseaseItem.className = 'list-group-item';
            
            let specializationsHtml = '';
            if (disease.specializations && disease.specializations.length > 0) {
                specializationsHtml = `
                    <p class="mb-1"><strong>Specializations:</strong> ${disease.specializations.join(', ')}</p>
                `;
            }
            
            let treatmentHtml = '';
            if (disease.treatment) {
                treatmentHtml = `
                    <p class="mb-1"><strong>Treatment:</strong> ${disease.treatment}</p>
                `;
            }
            
            diseaseItem.innerHTML = `
                <div class="d-flex w-100 justify-content-between">
                    <h5 class="mb-1">${disease.name}</h5>
                    <small class="text-${getSeverityClass(disease.severity_level)}">${disease.severity_level || 'Unknown'} severity</small>
                </div>
                <p class="mb-1">${disease.description || 'No description available.'}</p>
                ${specializationsHtml}
                ${treatmentHtml}
            `;
            
            diseasesList.appendChild(diseaseItem);
        });
    }
    
    // Function to display doctors
    function displayDoctors(doctors) {
        doctorsList.innerHTML = '';
        
        if (!doctors || doctors.length === 0) {
            doctorsList.innerHTML = '<div class="col-12"><div class="alert alert-info">No doctors available for the conditions found.</div></div>';
            return;
        }
        
        doctors.forEach(doctor => {
            const doctorCol = document.createElement('div');
            doctorCol.className = 'col-md-6 col-lg-4 mb-3';
            
            doctorCol.innerHTML = `
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">${doctor.name}</h5>
                        <h6 class="card-subtitle mb-2 text-muted">${doctor.specialization}</h6>
                        <p class="card-text">
                            <strong>Experience:</strong> ${doctor.experience_years} years<br>
                            <strong>Consultation Fee:</strong> $${doctor.consultation_fee}<br>
                            <strong>Email:</strong> ${doctor.email}
                        </p>
                        <a href="index.php?module=patient&action=book_appointment&doctor_id=${doctor.id}" class="btn btn-primary btn-sm">Book Appointment</a>
                    </div>
                </div>
            `;
            
            doctorsList.appendChild(doctorCol);
        });
    }
    
    // Helper function to get severity class
    function getSeverityClass(severity) {
        switch (severity) {
            case 'high':
                return 'danger';
            case 'medium':
                return 'warning';
            case 'low':
                return 'success';
            default:
                return 'secondary';
        }
    }

    function displayResults(data) {
        const resultsContainer = document.getElementById('search-results');
        resultsContainer.innerHTML = '';

        if (!data.diseases || data.diseases.length === 0) {
            resultsContainer.innerHTML = `
                <div class="alert alert-info mt-4">
                    <h4>No conditions found</h4>
                    <p>We couldn't find any conditions matching your symptoms. Please try adding more symptoms or consult with a general practitioner.</p>
                </div>
            `;
            return;
        }

        // Create diseases section
        const diseasesSection = document.createElement('div');
        diseasesSection.className = 'results-section';
        diseasesSection.innerHTML = `<h3 class="mb-4">Possible Conditions:</h3>`;

        // Add each disease
        data.diseases.forEach(disease => {
            const severityClass = disease.severity === 'high' ? 'severity-high' : 
                                 disease.severity === 'medium' ? 'severity-medium' : 'severity-low';
            
            const diseaseCard = document.createElement('div');
            diseaseCard.className = 'disease-card';
            diseaseCard.innerHTML = `
                <h4>${disease.name}</h4>
                <p class="${severityClass}">${disease.severity || 'medium'} severity</p>
                <p>${disease.description || 'No description available.'}</p>
                ${disease.specializations ? `<p><strong>Specializations:</strong> ${disease.specializations}</p>` : ''}
                ${disease.treatment ? `<p><strong>Treatment:</strong> ${disease.treatment}</p>` : ''}
            `;
            diseasesSection.appendChild(diseaseCard);
        });

        resultsContainer.appendChild(diseasesSection);

        // Create doctors section if doctors are available
        if (data.recommended_doctors && data.recommended_doctors.length > 0) {
            const doctorsSection = document.createElement('div');
            doctorsSection.className = 'results-section mt-4';
            doctorsSection.innerHTML = `<h3 class="mb-4">Recommended Doctors:</h3>`;

            // Add each doctor
            data.recommended_doctors.forEach(doctor => {
                const doctorCard = document.createElement('div');
                doctorCard.className = 'doctor-card';
                doctorCard.innerHTML = `
                    <h4>${doctor.name}</h4>
                    <p><strong>Specialization:</strong> ${doctor.specialization}</p>
                    <p><strong>Experience:</strong> ${doctor.experience_years} years</p>
                    <p><strong>Consultation Fee:</strong> $${doctor.consultation_fee}</p>
                    <p><strong>Email:</strong> ${doctor.email}</p>
                    <button class="book-appointment" data-doctor-id="${doctor.id}">Book Appointment</button>
                `;
                doctorsSection.appendChild(doctorCard);
            });

            resultsContainer.appendChild(doctorsSection);

            // Add event listeners for booking buttons
            document.querySelectorAll('.book-appointment').forEach(button => {
                button.addEventListener('click', function() {
                    const doctorId = this.getAttribute('data-doctor-id');
                    window.location.href = `index.php?module=patient&action=book_appointment&doctor_id=${doctorId}`;
                });
            });
        } else {
            // No doctors available
            const noDoctorsMessage = document.createElement('div');
            noDoctorsMessage.className = 'results-section mt-4';
            noDoctorsMessage.innerHTML = `
                <h3 class="mb-4">Recommended Doctors:</h3>
                <div class="alert alert-warning">
                    <p>No doctors are currently available for the conditions found. Please contact the hospital directly for assistance.</p>
                </div>
            `;
            resultsContainer.appendChild(noDoctorsMessage);
        }

        // Scroll to results
        resultsContainer.scrollIntoView({ behavior: 'smooth' });
    }
});