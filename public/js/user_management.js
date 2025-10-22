/**
 * User Management Helper Functions
 * สำหรับจัดการ roles และ user_type ของผู้ใช้
 */

// เก็บ cache ของ roles
let rolesCache = null;

/**
 * โหลด roles จาก API
 */
async function loadRoleOptions() {
    if (rolesCache) {
        return rolesCache;
    }

    try {
        const response = await fetch('{{ route("user_management.user_type_options") }}', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            }
        });

        const data = await response.json();
        if (data.success) {
            rolesCache = data.roles;
            return data.roles;
        }
    } catch (error) {
        console.error('Error loading roles:', error);
        showSnackbar('ไม่สามารถโหลด roles ได้', 'error');
    }
    return [];
}

/**
 * แสดง modal สำหรับแก้ไข roles ของผู้ใช้
 */
async function showEditRolesModal(userId) {
    try {
        // โหลดข้อมูลผู้ใช้
        const userResponse = await fetch(`{{ route('user_management.user_roles', ['id' => ':id']) }}`.replace(':id', userId), {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            }
        });

        const userData = await userResponse.json();
        if (!userData.success) {
            showSnackbar('ไม่สามารถโหลดข้อมูลผู้ใช้ได้', 'error');
            return;
        }

        const user = userData.user;
        const roles = await loadRoleOptions();

        // สร้าง modal
        let html = `
            <div class="modal fade" id="editRolesModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form id="editRolesForm" method="POST" action="{{ route('user_management.update_roles', ['id' => ':id']) }}">
                            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
                            <input type="hidden" name="_method" value="POST">
                            
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title">🔐 แก้ไข Role - ${user.name}</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">ชื่อผู้ใช้</label>
                                    <input type="text" class="form-control" value="${user.name}" disabled>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">User Type ปัจจุบัน</label>
                                    <input type="text" class="form-control" value="${user.usertype || '-'}" disabled>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">✅ เลือก Roles</label>
                                    <div id="rolesContainer">
        `;

        roles.forEach(role => {
            const isSelected = user.roles.includes(role.id);
            html += `
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="role_ids[]" 
                                   value="${role.id}" id="role_${role.id}" ${isSelected ? 'checked' : ''}>
                            <label class="form-check-label" for="role_${role.id}">
                                ${role.name}
                            </label>
                        </div>
            `;
        });

        html += `
                                    </div>
                                </div>

                                <div class="alert alert-info">
                                    <small>
                                        <strong>Note:</strong> Role แรกที่เลือกจะเป็น User Type หลัก
                                    </small>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                                <button type="submit" class="btn btn-primary">💾 บันทึก</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `;

        // ลบ modal เก่า ถ้ามี
        const oldModal = document.getElementById('editRolesModal');
        if (oldModal) {
            oldModal.remove();
        }

        // เพิ่ม modal ใหม่
        document.body.insertAdjacentHTML('beforeend', html);

        // แสดง modal
        const modal = new bootstrap.Modal(document.getElementById('editRolesModal'));
        modal.show();

        // จัดการ form submission
        document.getElementById('editRolesForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const roleIds = formData.getAll('role_ids[]');

            if (roleIds.length === 0) {
                showSnackbar('⚠️ กรุณาเลือก Role อย่างน้อย 1 อัน', 'warning');
                return;
            }

            // submit form
            this.submit();
        });

    } catch (error) {
        console.error('Error showing edit roles modal:', error);
        showSnackbar('เกิดข้อผิดพลาด: ' + error.message, 'error');
    }
}

/**
 * แสดง Snackbar notification
 */
function showSnackbar(message, type = 'info') {
    const snackbar = document.createElement('div');
    snackbar.className = `alert alert-${type} alert-dismissible fade show`;
    snackbar.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        max-width: 500px;
    `;
    snackbar.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    document.body.appendChild(snackbar);

    // Auto remove after 5 seconds
    setTimeout(() => {
        snackbar.remove();
    }, 5000);
}

/**
 * Approve user with roles
 */
async function approveUser(userId) {
    try {
        const roles = await loadRoleOptions();

        let html = `
            <div class="modal fade" id="approveUserModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form id="approveUserForm" method="POST">
                            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
                            
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title">✅ อนุมัติผู้ใช้</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">🔐 เลือก Roles</label>
                                    <div id="approveRolesContainer">
        `;

        roles.forEach(role => {
            html += `
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="role_ids[]" 
                                   value="${role.id}" id="approve_role_${role.id}">
                            <label class="form-check-label" for="approve_role_${role.id}">
                                ${role.name}
                            </label>
                        </div>
            `;
        });

        html += `
                                    </div>
                                </div>

                                <div class="alert alert-info">
                                    <small>
                                        <strong>Note:</strong> เลือก Role อย่างน้อย 1 อัน
                                    </small>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                                <button type="submit" class="btn btn-success">✅ อนุมัติ</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `;

        // ลบ modal เก่า ถ้ามี
        const oldModal = document.getElementById('approveUserModal');
        if (oldModal) {
            oldModal.remove();
        }

        // เพิ่ม modal ใหม่
        document.body.insertAdjacentHTML('beforeend', html);

        // แสดง modal
        const modal = new bootstrap.Modal(document.getElementById('approveUserModal'));
        modal.show();

        // จัดการ form submission
        document.getElementById('approveUserForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const roleIds = formData.getAll('role_ids[]');

            if (roleIds.length === 0) {
                showSnackbar('⚠️ กรุณาเลือก Role อย่างน้อย 1 อัน', 'warning');
                return;
            }

            this.action = `{{ route('user_management.approve', ['id' => ':id']) }}`.replace(':id', userId);
            this.submit();
        });

    } catch (error) {
        console.error('Error approving user:', error);
        showSnackbar('เกิดข้อผิดพลาด: ' + error.message, 'error');
    }
}
