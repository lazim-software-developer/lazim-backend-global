<script>
    var scrollToSection = function(event) {
        setTimeout(() => {
            const activeSidebarItem = document.querySelectorAll('.fi-sidebar-item');
            const sidebarWrapper = document.querySelector('.fi-sidebar-nav')
            const currentUrl = window.location.href;
            let groupToOpen = null;

            activeSidebarItem.forEach(item => {
                const anchor = item.querySelector('a');
                const anchorHref = anchor.getAttribute('href');
                const myEnvVar = "{{ env('APP_URL') }}";

                // Updated condition to properly handle both /admin and /app paths
                if (currentUrl.includes(anchorHref) &&
                    !(anchorHref === '/admin' || anchorHref === '/app') &&
                    (currentUrl.includes('/admin/') || currentUrl.includes('/app/'))) {
                    const activeItemOffsetTop = item.offsetTop;
                    const sidebarScrollPosition = activeItemOffsetTop - sidebarWrapper.offsetTop;
                    sidebarWrapper.scrollTo({
                        top: sidebarScrollPosition,
                        behavior: 'smooth'
                    });
                    // Add color class to the matched item
                    item.setAttribute("style", "background-color:lightgray;");

                    // Get the parent group of the matched item
                    const parentGroup = item.closest('[data-group-label]');

                    if (parentGroup) {
                        groupToOpen = parentGroup.dataset.groupLabel;
                    }
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
                // Skip toggling if it's the Dashboard
                if (groupLabel === 'Dashboard') return;

                // Check if the group is currently open
                if (!this.groupIsCollapsed(groupLabel)) {
                    this.collapsedGroups.push(groupLabel);
                } else {
                    this.collapsedGroups = this.collapsedGroups.filter(label => label !== groupLabel);
                }

                // Persist the collapsedGroups to localStorage
                localStorage.setItem('collapsedGroups', JSON.stringify(this.collapsedGroups));
            };

            if (groupToOpen !== 'Dashboard') {
                sidebarStore.toggleCollapsedGroup(groupToOpen);
            }
        }, 1)
    };

    document.addEventListener('livewire:navigated', scrollToSection);
    document.addEventListener('DOMContentLoaded', scrollToSection);
</script>
