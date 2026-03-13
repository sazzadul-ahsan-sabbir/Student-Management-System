// ============================================
// Student Management System - Main JavaScript
// ============================================

// API URL
const API_URL = 'http://localhost/backend/student.php';

// DOM Elements
const studentForm = document.getElementById('studentForm');
const submitBtn = document.getElementById('submitBtn');
const cancelBtn = document.getElementById('cancelBtn');
const refreshBtn = document.getElementById('refreshBtn');
const studentTableBody = document.getElementById('studentTableBody');
const searchInput = document.getElementById('searchInput');
const totalStudentsEl = document.getElementById('totalStudents');
const csStudentsEl = document.getElementById('csStudents');
const semesterAvgEl = document.getElementById('semesterAvg');
const deptCountEl = document.getElementById('deptCount');
const messageModal = document.getElementById('messageModal');
const modalMessage = document.getElementById('modalMessage');
const closeModal = document.querySelector('.close-modal');

// Global variables
let isEditing = false;
let currentStudentId = null;
let allStudents = [];

// ============================================
// Initialize when page loads
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    console.log('Student Management System Loading...');
    
    // Load students
    loadStudents();
    
    // Setup event listeners
    setupEventListeners();
    
    // Test API connection
    testAPIConnection();
});

// ============================================
// Setup Event Listeners
// ============================================
function setupEventListeners() {
    // Form submission
    studentForm.addEventListener('submit', handleFormSubmit);
    
    // Cancel edit
    cancelBtn.addEventListener('click', cancelEdit);
    
    // Refresh button
    refreshBtn.addEventListener('click', loadStudents);
    
    // Search functionality
    searchInput.addEventListener('input', handleSearch);
    
    // Reset form button
    const resetBtn = document.querySelector('.btn-reset');
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            studentForm.reset();
            cancelEdit();
        });
    }
    
    // Modal close
    closeModal.addEventListener('click', function() {
        messageModal.style.display = 'none';
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === messageModal) {
            messageModal.style.display = 'none';
        }
    });
}

// ============================================
// Handle Form Submission
// ============================================

// ============================================
// Handle Form Submission - DEBUG VERSION
// ============================================
function handleFormSubmit(e) {
    e.preventDefault();
    console.log('=== Form Submitted ===');
    
    // Get all form values
    const student_id = document.getElementById('student_id').value.trim();
    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const phone = document.getElementById('phone').value.trim();
    const department = document.getElementById('department').value;
    const semester = document.getElementById('semester').value;
    const address = document.getElementById('address').value.trim();
    
    console.log('Form values:');
    console.log('- Student ID:', student_id);
    console.log('- Name:', name);
    console.log('- Email:', email);
    console.log('- Phone:', phone);
    console.log('- Department:', department);
    console.log('- Semester:', semester);
    console.log('- Address:', address);
    
    const studentData = {
        student_id: student_id,
        name: name,
        email: email,
        phone: phone,
        department: department,
        semester: parseInt(semester) || 1,
        address: address
    };
    
    console.log('Student Data object:', studentData);
    
    // Validate form data
    if (!validateStudentData(studentData)) {
        console.log('Validation failed');
        return;
    }
    
    console.log('Validation passed');
    
    // Add or Update student
    if(isEditing) {
        console.log('Editing mode - updating student');
        studentData.id = currentStudentId;
        updateStudent(studentData);
    } else {
        console.log('Adding new student');
        addStudent(studentData);
    }
}

// ============================================
// Validate Student Data
// ============================================
function validateStudentData(data) {
    // Check required fields
    if (!data.student_id) {
        showMessage('Student ID is required', 'error');
        return false;
    }
    
    if (!data.name) {
        showMessage('Name is required', 'error');
        return false;
    }
    
    if (!data.email) {
        showMessage('Email is required', 'error');
        return false;
    }
    
    if (!data.department) {
        showMessage('Department is required', 'error');
        return false;
    }
    
    if (!data.semester) {
        showMessage('Semester is required', 'error');
        return false;
    }
    
    // Validate email format
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(data.email)) {
        showMessage('Please enter a valid email address', 'error');
        return false;
    }
    
    // Validate phone if provided
    if (data.phone && !/^[0-9+\-\s()]{10,15}$/.test(data.phone)) {
        showMessage('Please enter a valid phone number (10-15 digits)', 'error');
        return false;
    }
    
    return true;
}

// ============================================
// Handle Search
// ============================================
function handleSearch() {
    const searchTerm = this.value.toLowerCase();
    filterStudents(searchTerm);
}

// ============================================
// Filter Students
// ============================================
function filterStudents(searchTerm) {
    const filteredStudents = allStudents.filter(student => 
        student.student_id.toLowerCase().includes(searchTerm) ||
        student.name.toLowerCase().includes(searchTerm) ||
        student.email.toLowerCase().includes(searchTerm) ||
        student.department.toLowerCase().includes(searchTerm) ||
        student.semester.toString().includes(searchTerm)
    );
    
    renderStudents(filteredStudents);
}

// ============================================
// Load All Students
// ============================================
async function loadStudents() {
    try {
        console.log('Loading students from:', API_URL);
        
        // Show loading state
        studentTableBody.innerHTML = `
            <tr>
                <td colspan="6" style="text-align: center; padding: 40px;">
                    <div class="loading-state">
                        <i class="fas fa-spinner fa-spin"></i>
                        <p>Loading students...</p>
                    </div>
                </td>
            </tr>
        `;
        
        // Fetch students
        const response = await fetch(API_URL);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const students = await response.json();
        allStudents = students;
        
        // Render students
        renderStudents(students);
        
        // Update statistics
        updateStats(students);
        
        console.log(`Loaded ${students.length} students`);
        
    } catch(error) {
        console.error('Error loading students:', error);
        showErrorState('Failed to load students. ' + error.message);
    }
}

// ============================================
// Render Students to Table
// ============================================
function renderStudents(students) {
    studentTableBody.innerHTML = '';
    
    if (students.length === 0) {
        studentTableBody.innerHTML = `
            <tr>
                <td colspan="6" style="text-align: center; padding: 40px;">
                    <div class="empty-state">
                        <i class="fas fa-user-slash"></i>
                        <p>No students found</p>
                        <p style="color: #95a5a6; font-size: 14px; margin-top: 10px;">
                            Add a student using the form on the left
                        </p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    students.forEach((student, index) => {
        const row = document.createElement('tr');
        
        row.innerHTML = `
            <td>${index + 1}</td>
            <td><strong>${student.student_id}</strong></td>
            <td>
                <div class="student-info">
                    <div class="student-name">${student.name}</div>
                    <div class="student-email">${student.email}</div>
                </div>
            </td>
            <td><span class="dept-badge">${student.department}</span></td>
            <td><span class="semester-badge">Semester ${student.semester}</span></td>
            <td class="action-buttons">
                <button class="btn-view" onclick="viewStudent(${student.id})" title="View Details">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn-edit" onclick="editStudent(${student.id})" title="Edit">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-delete" onclick="deleteStudent(${student.id})" title="Delete">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        
        studentTableBody.appendChild(row);
    });
}

// ============================================
// View Student Details
// ============================================
function viewStudent(id) {
    const student = allStudents.find(s => s.id == id);
    if (student) {
        const details = `
            <h3><i class="fas fa-user-graduate"></i> Student Details</h3>
            <div class="student-details">
                <p><strong>Student ID:</strong> ${student.student_id}</p>
                <p><strong>Name:</strong> ${student.name}</p>
                <p><strong>Email:</strong> ${student.email}</p>
                <p><strong>Phone:</strong> ${student.phone || 'N/A'}</p>
                <p><strong>Department:</strong> ${student.department}</p>
                <p><strong>Semester:</strong> ${student.semester}</p>
                <p><strong>Address:</strong> ${student.address || 'N/A'}</p>
                <p><strong>Added on:</strong> ${formatDate(student.created_at)}</p>
            </div>
        `;
        
        showMessageModal(details, 'Student Details');
    }
}

// ============================================
// Edit Student
// ============================================
async function editStudent(id) {
    try {
        const response = await fetch(`${API_URL}?id=${id}`);
        const student = await response.json();
        
        // Fill form with student data
        document.getElementById('studentId').value = student.id;
        document.getElementById('student_id').value = student.student_id;
        document.getElementById('name').value = student.name;
        document.getElementById('email').value = student.email;
        document.getElementById('phone').value = student.phone || '';
        document.getElementById('department').value = student.department;
        document.getElementById('semester').value = student.semester;
        document.getElementById('address').value = student.address || '';
        
        // Change to edit mode
        isEditing = true;
        currentStudentId = student.id;
        submitBtn.innerHTML = '<i class="fas fa-edit"></i> Update Student';
        cancelBtn.style.display = 'inline-flex';
        
        // Scroll to form
        document.querySelector('.form-section').scrollIntoView({ behavior: 'smooth' });
        
        showMessage('Editing student: ' + student.name, 'info');
        
    } catch(error) {
        console.error('Error fetching student:', error);
        showMessage('Failed to load student data', 'error');
    }
}

// ============================================
// Add New Student
// ============================================
// ============================================
// Add New Student - DEBUG VERSION
// ============================================
async function addStudent(studentData) {
    console.log('=== DEBUG: Starting addStudent ===');
    console.log('Student Data to send:', studentData);
    console.log('API URL:', API_URL);
    
    try {
        // Disable submit button
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        
        console.log('Sending POST request to API...');
        
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(studentData)
        });
        
        console.log('Response received!');
        console.log('Response Status:', response.status);
        console.log('Response OK?', response.ok);
        
        // First get the response as text
        const responseText = await response.text();
        console.log('Raw Response Text:', responseText);
        
        let result;
        try {
            // Try to parse as JSON
            result = JSON.parse(responseText);
            console.log('Parsed JSON Response:', result);
        } catch (e) {
            console.error('JSON Parse Error:', e);
            console.error('Could not parse response as JSON');
            console.error('Raw response was:', responseText);
            
            showMessage('Server returned invalid response. Check backend setup.', 'error');
            return;
        }
        
        // Check if success or error
        if (result.success === true) {
            console.log('✓ Student added successfully!');
            showMessage('Student added successfully!', 'success');
            studentForm.reset();
            loadStudents(); // Reload students
        } else if (result.error === true) {
            console.error('✗ API returned error:', result.message);
            showMessage(result.message || 'Failed to add student', 'error');
        } else {
            console.warn('Unknown response format:', result);
            showMessage('Unexpected response from server', 'error');
        }
        
    } catch(error) {
        console.error('Network/Fetch Error:', error);
        console.error('Error name:', error.name);
        console.error('Error message:', error.message);
        
        showMessage('Network error: ' + error.message, 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save"></i> Save Student';
    }
}

// ============================================
// Update Student
// ============================================
async function updateStudent(studentData) {
    try {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
        
        const response = await fetch(API_URL, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(studentData)
        });
        
        const result = await response.json();
        
        if (result.error) {
            showMessage(result.message || 'Failed to update student', 'error');
        } else {
            showMessage('Student updated successfully!', 'success');
            cancelEdit();
            loadStudents();
        }
    } catch(error) {
        console.error('Error updating student:', error);
        showMessage('Failed to update student', 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-edit"></i> Update Student';
    }
}

// ============================================
// Delete Student
// ============================================
async function deleteStudent(id) {
    const student = allStudents.find(s => s.id == id);
    if (!student) return;
    
    if(!confirm(`Are you sure you want to delete student: ${student.name} (${student.student_id})?`)) {
        return;
    }
    
    try {
        const response = await fetch(`${API_URL}?id=${id}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.error) {
            showMessage(result.message || 'Failed to delete student', 'error');
        } else {
            showMessage('Student deleted successfully!', 'success');
            loadStudents();
        }
    } catch(error) {
        console.error('Error deleting student:', error);
        showMessage('Failed to delete student', 'error');
    }
}

// ============================================
// Cancel Edit Mode
// ============================================
function cancelEdit() {
    isEditing = false;
    currentStudentId = null;
    studentForm.reset();
    document.getElementById('studentId').value = '';
    submitBtn.innerHTML = '<i class="fas fa-save"></i> Save Student';
    cancelBtn.style.display = 'none';
}

// ============================================
// Update Statistics
// ============================================
function updateStats(students) {
    // Total students
    totalStudentsEl.textContent = students.length;
    
    // Computer Science students
    const csStudents = students.filter(s => s.department === 'Computer Science').length;
    csStudentsEl.textContent = csStudents;
    
    // Average semester
    if(students.length > 0) {
        const totalSemester = students.reduce((sum, student) => sum + parseInt(student.semester), 0);
        const avgSemester = (totalSemester / students.length).toFixed(1);
        semesterAvgEl.textContent = avgSemester;
    } else {
        semesterAvgEl.textContent = '0';
    }
    
    // Unique departments
    const uniqueDepts = new Set(students.map(s => s.department));
    deptCountEl.textContent = uniqueDepts.size;
}

// ============================================
// Show Message Modal
// ============================================
function showMessageModal(content, title = 'Message') {
    modalMessage.innerHTML = `
        <h3>${title}</h3>
        ${content}
    `;
    messageModal.style.display = 'block';
}

// ============================================
// Show Message
// ============================================
function showMessage(message, type = 'info') {
    // Create message element
    const messageDiv = document.createElement('div');
    messageDiv.className = `message-${type}`;
    messageDiv.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : 
                      type === 'error' ? 'fa-exclamation-circle' : 
                      'fa-info-circle'}"></i>
        <span>${message}</span>
    `;
    
    // Style the message
    messageDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        background: ${type === 'success' ? '#28a745' : 
                    type === 'error' ? '#dc3545' : 
                    '#17a2b8'};
        color: white;
        border-radius: 5px;
        z-index: 1000;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        animation: slideIn 0.3s ease-out;
    `;
    
    // Add animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
    `;
    document.head.appendChild(style);
    
    document.body.appendChild(messageDiv);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        messageDiv.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => {
            if (messageDiv.parentNode) {
                document.body.removeChild(messageDiv);
            }
        }, 300);
    }, 3000);
}

// ============================================
// Show Error State
// ============================================
function showErrorState(errorMessage) {
    studentTableBody.innerHTML = `
        <tr>
            <td colspan="6" style="text-align: center; padding: 40px;">
                <div style="color: #e74c3c;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 48px;"></i>
                    <h3>Failed to Load Students</h3>
                    <p style="color: #7f8c8d;">${errorMessage}</p>
                </div>
                <div style="margin-top: 20px;">
                    <button onclick="loadStudents()" class="btn-submit">
                        <i class="fas fa-redo"></i> Try Again
                    </button>
                </div>
            </td>
        </tr>
    `;
}

// ============================================
// Test API Connection
// ============================================
async function testAPIConnection() {
    try {
        const response = await fetch(API_URL);
        if (response.ok) {
            console.log('✓ API Connection Successful');
        } else {
            console.error('✗ API Connection Failed:', response.status);
            showMessage('API Connection Failed. Please check backend setup.', 'error');
        }
    } catch (error) {
        console.error('✗ API Connection Error:', error);
    }
}

// ============================================
// Utility Functions
// ============================================
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// ============================================
// Global Functions (for HTML onclick)
// ============================================
window.viewStudent = viewStudent;
window.editStudent = editStudent;
window.deleteStudent = deleteStudent;