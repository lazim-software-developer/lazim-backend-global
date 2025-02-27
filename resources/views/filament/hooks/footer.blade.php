<script>
    var scrollToSection = function(event) {
        setTimeout(() => {
            const activeSidebarItem = document.querySelectorAll('.fi-sidebar-item');
            const sidebarWrapper = document.querySelector('.fi-sidebar-nav');
            const currentUrl = window.location.href;
            let groupToOpen = null;

            activeSidebarItem.forEach(item => {
                const anchor = item.querySelector('a');
                const anchorHref = anchor.getAttribute('href');
                const myEnvVar = "{{ env('APP_URL') }}";

                // Combine scrolling logic into a single function
                const scrollToItem = (item) => {
                    const activeItemOffsetTop = item.offsetTop;
                    const sidebarScrollPosition = activeItemOffsetTop - sidebarWrapper.offsetTop;
                    sidebarWrapper.scrollTo({
                        top: sidebarScrollPosition,
                        behavior: 'smooth'
                    });
                    item.setAttribute("style", "background-color:lightgray;");
                    const parentGroup = item.closest('[data-group-label]');
                    if (parentGroup) {
                        groupToOpen = parentGroup.dataset.groupLabel;
                    }
                };

                // Check conditions and scroll
                if ((currentUrl.startsWith(myEnvVar + '/app/complaintsenquiries') && anchorHref === '/app/complaintsenquiries') ||
                    (currentUrl.startsWith(myEnvVar + '/app/complaintsenquiries/') && anchorHref === '/app/complaintsenquiries') ||
                    (currentUrl.startsWith(myEnvVar + '/app/complaintssuggessions') && anchorHref === '/app/complaintssuggessions') ||
                    (currentUrl.startsWith(myEnvVar + '/app/complaintssuggessions/') && anchorHref === '/app/complaintssuggessions') ||
                    (!currentUrl.startsWith(myEnvVar + '/app/complaintsenquiries') && !currentUrl.startsWith(myEnvVar + '/app/complaintssuggessions') && currentUrl.includes(anchorHref) &&
                    !(anchorHref === '/admin' || anchorHref === '/app') &&
                    (currentUrl.includes('/admin/') || currentUrl.includes('/app/')))) {
                    scrollToItem(item);
                }

                // Add specific highlight for dashboard
                if ((currentUrl.endsWith('/admin') && anchorHref === '/admin') ||
                    (currentUrl.endsWith('/app') && anchorHref === '/app')) {
                    item.setAttribute("style", "background-color:lightgray;");
                }
            });

            // Handle Sidebar collapse
            const sidebarStore = window.Alpine.store('sidebar');
            // Hide Dashboard label text and button
            document.querySelectorAll('[data-group-label]').forEach(el => {
                if (el.dataset.groupLabel === 'Dashboard') {
                    const labelElement = el.querySelector('.fi-sidebar-group-button span');
                    if (labelElement) {
                        labelElement.textContent = '';
                    }
                    const button = el.querySelector('.fi-sidebar-group-button');
                    if (button) {
                        button.style.display = 'none';
                    }
                }
            });

            // Extract data-group-label values and filter out "Dashboard"
            const labels = Array.from(document.querySelectorAll('[data-group-label]'))
                .map(el => el.dataset.groupLabel)
                .filter(label => label !== 'Dashboard');

            localStorage.setItem('collapsedGroups', JSON.stringify(labels));
            sidebarStore.collapsedGroups = labels;

            sidebarStore.toggleCollapsedGroup = function(groupLabel) {
                if (groupLabel === 'Dashboard') return;
                if (!this.groupIsCollapsed(groupLabel)) {
                    this.collapsedGroups.push(groupLabel);
                } else {
                    this.collapsedGroups = this.collapsedGroups.filter(label => label !== groupLabel);
                }
                localStorage.setItem('collapsedGroups', JSON.stringify(this.collapsedGroups));
            };

            if (groupToOpen !== 'Dashboard') {
                sidebarStore.toggleCollapsedGroup(groupToOpen);
            }
        }, 100); // Increased timeout to allow for DOM readiness
    };

    document.addEventListener('livewire:navigated', scrollToSection);
    document.addEventListener('DOMContentLoaded', scrollToSection);
</script>
