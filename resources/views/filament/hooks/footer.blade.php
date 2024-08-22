<script>
    var scrollToSection = function(event) {
        setTimeout(() => {
            const activeSidebarItem = document.querySelectorAll('.fi-sidebar-item');
            const sidebarWrapper = document.querySelector('.fi-sidebar-nav')
            const currentUrl = window.location.href;
            // console.log(activeSidebarItem);
            // console.log(currentUrl);
            let groupToOpen = null;

            activeSidebarItem.forEach(item => {
                const anchor = item.querySelector('a');
                const anchorHref = anchor.getAttribute('href');
                const myEnvVar = "{{ env('APP_URL') }}";
                // console.log(myEnvVar+anchorHref);
                // console.log(item);
                if (currentUrl.includes(anchorHref) && anchorHref != '/admin') {
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
            });

            // Handle Sidebar collapse
            const sidebarStore = window.Alpine.store('sidebar');
            // Extract data-group-label values from these elements
            const labels = Array.from(document.querySelectorAll('[data-group-label]')).map(el => el.dataset.groupLabel);
            localStorage.setItem('collapsedGroups', JSON.stringify(labels));
            sidebarStore.collapsedGroups = labels;

            sidebarStore.toggleCollapsedGroup = function(groupLabel) {
                // Check if the group is currently open
                if (!this.groupIsCollapsed(groupLabel)) {
                    // If the group is already open, collapse it
                    this.collapsedGroups.push(groupLabel);
                } else {
                    this.collapsedGroups = this.collapsedGroups.filter(label => label !== groupLabel);
                }

                // Persist the collapsedGroups to localStorage
                localStorage.setItem('collapsedGroups', JSON.stringify(this.collapsedGroups));
            };

            sidebarStore.toggleCollapsedGroup(groupToOpen);
        }, 1)
    };

    document.addEventListener('livewire:navigated', scrollToSection);
    document.addEventListener('DOMContentLoaded', scrollToSection);
</script>
