$(document).ready(function () {
    $('.searchArticleBtn').on('click', function () {
        const searchText = $(this).parent().find('.searchArticleIn').val().trim();
        if (searchText.length < 3) {
            showToaster("La recherche doit contenir au moins 3 caractères", "white", 4000);
            return;
        }
        window.location.href = `/actualites/?q=${searchText}`;
    });
    $('.searchArticleIn').on("keyup", function (event) {
        const searchText = $(this).val().trim();
        if (event.key === 'Enter') {
            if (searchText.length < 3) {
                showToaster("La recherche doit contenir au moins 3 caractères", "white", 4000);
                return;
            }
            window.location.href = `/actualites/?q=${searchText}`;
        }
    });
});