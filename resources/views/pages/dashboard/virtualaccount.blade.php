        <form class="row g-3">
                <div class="col-12">
                <label class="form-label">Account Name</label>
                <input type="text" class="form-control" readonly value="{{ $virtualAccount->accountName }}">
                </div>
                <div class="col-12">
                <label class="form-label">Account Number</label>
                <input type="text" class="form-control" readonly value="{{ $virtualAccount->accountNo }}">
                </div>
                <div class="col-12">
                <label class="form-label">Bank Name</label>
                <input type="text" class="form-control" readonly value="{{ $virtualAccount->bankName }}">
                </div>
              </form>
             