import "preline";
import Swal from "sweetalert2";

document.addEventListener("livewire:navigated", () => {
    window.HSStaticMethods.autoInit();
});

window.Swal = Swal;
