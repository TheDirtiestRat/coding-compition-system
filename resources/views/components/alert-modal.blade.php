
<dialog id="alert_modal_1" class="modal rounded-xl" aria-labelledby="confirmation-title">
    <div class="modal-box rounded-xl p-4">
        <h3 id="confirmation-title" class="text-lg font-semibold">Confirm action</h3>
        <p class="py-2" id="alert_modal_message">Press ESC key or click the button below to close</p>
        <div class="modal-action mt-3">
            
            <form method="dialog" class="flex justify-center w-full gap-4">
                <!-- if there is a button in form, it will close the modal -->
                <button class="btn w-20" id="accept_alert_btn" value="accept">Confirm</button>
                <button class="btn w-20" value="cancel">Cancel</button>
            </form>
        </div>
    </div>
</dialog>
