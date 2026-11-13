
<dialog id="alert_modal_1" class="modal rounded-xl">
    <div class="modal-box rounded-xl p-4">
        <h3 class="text-lg font-bold">Alert!</h3>
        <p class="py-2" id="alert_modal_message">Press ESC key or click the button below to close</p>
        <div class="modal-action mt-3">
            
            <form method="dialog" class="flex justify-center w-full gap-4">
                <!-- if there is a button in form, it will close the modal -->
                <button class="btn w-20" id="accept_alert_btn" value="accept">Accept</button>
                <button class="btn w-20" value="cancel">Close</button>
            </form>
        </div>
    </div>
</dialog>
