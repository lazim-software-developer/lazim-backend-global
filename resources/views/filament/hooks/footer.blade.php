<script>
    var scrollToSection = function(event) {
        setTimeout(() => {
            const activeSidebarItem = document.querySelectorAll('.fi-sidebar-item');
            const sidebarWrapper = document.querySelector('.fi-sidebar-nav')
            const currentUrl = window.location.href;
            // console.log(activeSidebarItem);
            // console.log(currentUrl);

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
                }
            });
        }, 1)
    };

    document.addEventListener('livewire:navigated', scrollToSection);
    document.addEventListener('DOMContentLoaded', scrollToSection);
</script>