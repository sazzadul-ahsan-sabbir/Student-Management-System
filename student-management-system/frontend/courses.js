// API URLs - Update according to your setup
const API_URL = 'http://localhost/backend/student.php';
const COURSE_API = 'http://localhost/backend/course.php';
const ENROLLMENT_API = 'http://localhost/backend/enrollment.php';

// Global variables
let allCourses = [];
let allStudents = [];

// DOM Content Loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('Course Management System Loading...');
    
    // Load courses initially
    loadCourses();
    
    // Setup event listeners
    setupEventListeners();
});

// Setup event listeners
function setupEventListeners() {
    // Tab switching
    const tabButtons = document.querySelectorAll('.tab-btn');
    tabButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const tabName = this.getAttribute('onclick').replace("openTab('", "").replace("')", "");
            openTab(tabName);
        });
    });
    
    // Course form submission
    const courseForm = document.getElementById('courseForm');
    if (courseForm) {
        courseForm.addEventListener('submit', function(e) {
            e.preventDefault();
            addNewCourse();
        });
    }
    
    // Enroll button
    const enrollBtn = document.getElementById('enrollBtn');
    if (enrollBtn) {
        enrollBtn.addEventListener('click', enrollStudent);
    }
    
    // Modal close
    const closeModal = document.querySelector('.close-modal');
    if (closeModal) {
        closeModal.addEventListener('click', () => {
            document.getElementById('messageModal').style.display = 'none';
        });
    }
    
    // Search filter
    const searchInput = document.getElementById('courseSearch');
    if (searchInput) {
        searchInput.addEventListener('input', filterCourses);
    }
    
    // Department filter
    const deptFilter = document.getElementById('departmentFilter');
    if (deptFilter) {
        deptFilter.addEventListener('change', filterCourses);
    }
    
    // Semester filter
    const semesterFilter = document.getElementById('semesterFilter');
    if (semesterFilter) {
        semesterFilter.addEventListener('change', filterCourses);
    }
}

// Tab functionality
function openTab(tabName) {
    console.log('Opening tab:', tabName);
    
    // Hide all tab contents
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(tab => tab.classList.remove('active'));
    
    // Remove active class from all tab buttons
    const tabButtons = document.querySelectorAll('.tab-btn');
    tabButtons.forEach(btn => btn.classList.remove('active'));
    
    // Show selected tab
    const selectedTab = document.getElementById(tabName);
    if (selectedTab) {
        selectedTab.classList.add('active');
    }
    
    // Add active class to clicked button
    event.currentTarget.classList.add('active');
    
    // Load data based on tab
    if (tabName === 'courses') {
        loadCourses();
    } else if (tabName === 'enrollments') {
        loadEnrollmentData();
    }
}

// Load courses
async function loadCourses() {
    console.log('Loading courses from:', COURSE_API);
    
    try {
        const response = await fetch(COURSE_API);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Courses data:', data);
        
        // Handle response format
        if (Array.isArray(data)) {
            allCourses = data;
            renderCourses(allCourses);
            updateCourseStats(data);
            showMessage(`Loaded ${data.length} courses`, 'success');
        } else if (data.error) {
            throw new Error(data.message || 'API returned error');
        } else {
            throw new Error('Invalid response format');
        }
        
    } catch(error) {
        console.error('Error loading courses:', error);
        showErrorState(error.message);
    }
}

// Render courses to table
function renderCourses(courses) {
    const tbody = document.getElementById('coursesTableBody');
    if (!tbody) return;
    
    if (!courses || courses.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" style="text-align: center; padding: 40px;">
                    <i class="fas fa-book" style="font-size: 48px; color: #ccc;"></i>
                    <p>No courses found</p>
                    <button onclick="loadCourses()" class="btn-submit" style="margin-top: 10px;">
                        <i class="fas fa-redo"></i> Refresh
                    </button>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = '';
    
    courses.forEach((course, index) => {
        const row = document.createElement('tr');
        
        row.innerHTML = `
            <td>${index + 1}</td>
            <td><strong>${course.course_code || 'N/A'}</strong></td>
            <td>${course.course_name || 'N/A'}</td>
            <td><span class="dept-badge">${course.department || 'N/A'}</span></td>
            <td>${course.semester || 'N/A'}</td>
            <td>${course.credits || 0}</td>
            <td>${course.instructor_name || 'Not assigned'}</td>
            <td class="action-buttons">
                <button class="btn-view" onclick="viewCourse(${course.course_id || index})" title="View">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn-edit" onclick="editCourse(${course.course_id || index})" title="Edit">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-delete" onclick="deleteCourse(${course.course_id || index})" title="Delete">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        
        tbody.appendChild(row);
    });
}

// Update course statistics
function updateCourseStats(courses) {
    const totalCoursesEl = document.getElementById('totalCourses');
    const csCoursesEl = document.getElementById('csCourses');
    const instructorsCountEl = document.getElementById('instructorsCount');
    
    if (totalCoursesEl) totalCoursesEl.textContent = courses.length;
    
    // Count CS courses
    const csCourses = courses.filter(course => 
        course.department && course.department.toLowerCase().includes('computer')
    ).length;
    if (csCoursesEl) csCoursesEl.textContent = csCourses;
    
    // Count unique instructors
    const instructors = new Set();
    courses.forEach(course => {
        if (course.instructor_name && course.instructor_name.trim() !== '') {
            instructors.add(course.instructor_name);
        }
    });
    if (instructorsCountEl) instructorsCountEl.textContent = instructors.size;
}

// Filter courses
function filterCourses() {
    const searchInput = document.getElementById('courseSearch');
    const deptFilter = document.getElementById('departmentFilter');
    const semesterFilter = document.getElementById('semesterFilter');
    
    if (!searchInput || !allCourses) return;
    
    const searchTerm = searchInput.value.toLowerCase();
    const selectedDept = deptFilter ? deptFilter.value : '';
    const selectedSemester = semesterFilter ? semesterFilter.value : '';
    
    const filtered = allCourses.filter(course => {
        const matchesSearch = 
            (course.course_code && course.course_code.toLowerCase().includes(searchTerm)) ||
            (course.course_name && course.course_name.toLowerCase().includes(searchTerm)) ||
            (course.instructor_name && course.instructor_name.toLowerCase().includes(searchTerm));
        
        const matchesDept = !selectedDept || course.department === selectedDept;
        const matchesSemester = !selectedSemester || course.semester == selectedSemester;
        
        return matchesSearch && matchesDept && matchesSemester;
    });
    
    renderCourses(filtered);
}

// Add new course
async function addNewCourse() {
    const form = document.getElementById('courseForm');
    if (!form) return;
    
    const formData = {
        course_code: document.getElementById('course_code').value.trim(),
        course_name: document.getElementById('course_name').value.trim(),
        department: document.getElementById('department').value,
        credits: parseInt(document.getElementById('credits').value) || 3,
        semester: parseInt(document.getElementById('semester').value) || 1,
        instructor_name: document.getElementById('instructor_name').value.trim(),
        description: document.getElementById('description').value.trim()
    };
    
    // Validation
    if (!formData.course_code || !formData.course_name || !formData.department) {
        showMessage('Please fill all required fields', 'error');
        return;
    }
    
    try {
        const response = await fetch(COURSE_API, {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const result = await response.json();
        console.log('Add course response:', result);
        
        if (result.success || !result.error) {
            showMessage('Course added successfully!', 'success');
            form.reset();
            loadCourses(); // Reload courses
            openTab('courses'); // Switch to courses tab
        } else {
            showMessage(result.message || 'Failed to add course', 'error');
        }
    } catch (error) {
        console.error('Error adding course:', error);
        showMessage('Error: ' + error.message, 'error');
    }
}

// Load enrollment data
async function loadEnrollmentData() {
    try {
        // Load students for dropdown
        const studentsResponse = await fetch(API_URL);
        allStudents = await studentsResponse.json();
        
        // Populate student dropdown
        const studentSelect = document.getElementById('studentSelect');
        if (studentSelect) {
            studentSelect.innerHTML = '<option value="">Select student</option>';
            allStudents.forEach(student => {
                const option = document.createElement('option');
                option.value = student.id;
                option.textContent = `${student.student_id} - ${student.name}`;
                studentSelect.appendChild(option);
            });
        }
        
        // Load courses for dropdown
        const coursesResponse = await fetch(COURSE_API);
        const courses = await coursesResponse.json();
        
        // Populate course dropdown
        const courseSelect = document.getElementById('courseSelect');
        if (courseSelect) {
            courseSelect.innerHTML = '<option value="">Select course</option>';
            if (Array.isArray(courses)) {
                courses.forEach(course => {
                    const option = document.createElement('option');
                    option.value = course.course_id;
                    option.textContent = `${course.course_code} - ${course.course_name}`;
                    courseSelect.appendChild(option);
                });
            }
        }
        
        // Load enrollments
        const enrollResponse = await fetch(ENROLLMENT_API);
        const enrollments = await enrollResponse.json();
        renderEnrollments(enrollments);
        
    } catch (error) {
        console.error('Error loading enrollment data:', error);
        showMessage('Failed to load enrollment data', 'error');
    }
}

// Render enrollments
function renderEnrollments(enrollments) {
    const tbody = document.getElementById('enrollmentsTableBody');
    if (!tbody) return;
    
    if (!enrollments || enrollments.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" style="text-align: center; padding: 40px;">
                    <i class="fas fa-user-graduate" style="font-size: 48px; color: #ccc;"></i>
                    <p>No enrollments found</p>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = '';
    
    enrollments.forEach((enrollment, index) => {
        const row = document.createElement('tr');
        
        row.innerHTML = `
            <td>${index + 1}</td>
            <td>${enrollment.student_name || enrollment.name || 'N/A'}</td>
            <td>${enrollment.course_code || 'N/A'} - ${enrollment.course_name || 'N/A'}</td>
            <td>${enrollment.enrollment_date || 'N/A'}</td>
            <td>
                <span class="status-badge ${(enrollment.status || 'Enrolled').toLowerCase()}">
                    ${enrollment.status || 'Enrolled'}
                </span>
            </td>
            <td class="action-buttons">
                <button class="btn-edit" onclick="updateEnrollment(${enrollment.enrollment_id})" title="Update">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-delete" onclick="deleteEnrollment(${enrollment.enrollment_id})" title="Delete">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        
        tbody.appendChild(row);
    });
}

// Enroll student
async function enrollStudent() {
    const studentId = document.getElementById('studentSelect').value;
    const courseId = document.getElementById('courseSelect').value;
    
    if (!studentId || !courseId) {
        showMessage('Please select both student and course', 'error');
        return;
    }
    
    const enrollmentData = {
        student_id: parseInt(studentId),
        course_id: parseInt(courseId)
    };
    
    try {
        const response = await fetch(ENROLLMENT_API, {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(enrollmentData)
        });
        
        const result = await response.json();
        
        if (result.success || !result.error) {
            showMessage('Student enrolled successfully!', 'success');
            loadEnrollmentData(); // Reload enrollments
            // Clear selections
            document.getElementById('studentSelect').value = '';
            document.getElementById('courseSelect').value = '';
        } else {
            showMessage(result.message || 'Failed to enroll student', 'error');
        }
    } catch (error) {
        console.error('Error enrolling student:', error);
        showMessage('Error: ' + error.message, 'error');
    }
}

// Show message
function showMessage(message, type = 'info') {
    console.log(`${type}: ${message}`);
    
    const modal = document.getElementById('messageModal');
    const modalMessage = document.getElementById('modalMessage');
    
    if (!modal || !modalMessage) {
        // Create temporary alert
        alert(`${type.toUpperCase()}: ${message}`);
        return;
    }
    
    modalMessage.innerHTML = `
        <div class="message-${type}">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 
                          type === 'error' ? 'fa-exclamation-circle' : 
                          'fa-info-circle'}"></i>
            <p>${message}</p>
        </div>
    `;
    
    modal.style.display = 'block';
    
    // Auto close after 3 seconds for success messages
    if (type === 'success') {
        setTimeout(() => {
            modal.style.display = 'none';
        }, 3000);
    }
}

// Show error state
function showErrorState(errorMessage) {
    const tbody = document.getElementById('coursesTableBody');
    if (!tbody) return;
    
    tbody.innerHTML = `
        <tr>
            <td colspan="8" style="text-align: center; padding: 40px;">
                <div style="color: #e74c3c; margin-bottom: 20px;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 48px;"></i>
                    <h3>Failed to Load Courses</h3>
                    <p style="color: #7f8c8d;">${errorMessage}</p>
                </div>
                <div style="margin-top: 20px;">
                    <button onclick="loadCourses()" class="btn-submit" style="margin: 5px;">
                        <i class="fas fa-redo"></i> Retry
                    </button>
                    <button onclick="testAPIConnection()" class="btn-submit" style="margin: 5px;">
                        <i class="fas fa-plug"></i> Test Connection
                    </button>
                </div>
            </td>
        </tr>
    `;
}

// Test API connection
async function testAPIConnection() {
    showMessage('Testing API connection...', 'info');
    
    try {
        const response = await fetch(COURSE_API);
        const data = await response.json();
        
        if (Array.isArray(data)) {
            showMessage(`API is working! Found ${data.length} courses`, 'success');
        } else {
            showMessage('API returned unexpected format', 'error');
        }
    } catch (error) {
        showMessage('API Connection Failed: ' + error.message, 'error');
    }
}

// Basic course functions (to be implemented)
function viewCourse(courseId) {
    showMessage(`View course ${courseId} - Feature coming soon`, 'info');
}

function editCourse(courseId) {
    showMessage(`Edit course ${courseId} - Feature coming soon`, 'info');
}

function deleteCourse(courseId) {
    if (confirm('Are you sure you want to delete this course?')) {
        showMessage(`Delete course ${courseId} - Feature coming soon`, 'info');
    }
}

function updateEnrollment(enrollmentId) {
    showMessage(`Update enrollment ${enrollmentId} - Feature coming soon`, 'info');
}

function deleteEnrollment(enrollmentId) {
    if (confirm('Are you sure you want to delete this enrollment?')) {
        showMessage(`Delete enrollment ${enrollmentId} - Feature coming soon`, 'info');
    }
}