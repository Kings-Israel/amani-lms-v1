<h5 class="my-2 text-primary">Profession/Industry</h5>

<div class="form-group row">

    <!-- industry type -->
    <div class="col-md-3 my-1">
        <label class="block">Industry Type</label>
        <div class="input-group">
            <span class="input-group-addon"><i class="icofont icofont-ui-office"></i></span>

            <select name="industry_type" class="js-example-basic-single">
                <optgroup>
                    <option>Select Industry</option>
                    <option value="1">Manufacturing</option>
                    <option value="2">Retail</option>
                    <option value="2">Agriculture</option>
                </optgroup>
            </select>
        </div>
    </div>

    <!-- business type -->
    <div class="col-md-3 my-1">
        <label class="block">Business Type</label>
        <div class="input-group">
            <span class="input-group-addon"><i class="icofont icofont-ui-office"></i></span>

            <select name="business_type" class="js-example-basic-single">
                <optgroup>
                    <option>Business Type</option>
                    <option value="1">SME</option>
                    <option value="2">ME</option>
                    <option value="2">LE</option>
                </optgroup>
            </select>
        </div>
    </div>

    <!-- is employed -->
    <div class="col-md-3 my-1">
        <label class="block d-block">Is Employed?</label>

        <input name="is_employed" type="checkbox" value="" class="my-2">
    </div>

    <div class="col-md-3-my-1 offset-md-2"></div>


    <!-- employment status -->
    <div class="col-md-3 my-1">
        <label class="block">Employment Status</label>
        <div class="input-group">
            <span class="input-group-addon"><i class="icofont icofont-ui-user"></i></span>

            <select name="employment_status" class="js-example-basic-single">
                <optgroup>
                    <option value="1">Employment Status</option>
                    <option value="1">Self Employed</option>
                    <option value="2">Employed</option>
                </optgroup>
            </select>
        </div>
    </div>

    <!-- employer -->
    <div class="col-md-3 my-1">
        <label class="block">Employer</label>
        <div class="input-group">
            <span class="input-group-addon"><i class="icofont icofont-ui-user"></i></span>

            <select name="employer" class="js-example-basic-single">
                <optgroup>
                    <option value="1">Select Employer</option>
                    <option value="1">Sally Doe</option>
                    <option value="2">Billy Doe</option>
                    <option value="2">John Doe</option>
                </optgroup>
            </select>
        </div>
    </div>

    <div class="col-md-3-my-1 offset-md-6"></div>

    <!-- date of employment -->
    <div class="col-md-3 my-1">
        <label class="block">Date of Employment</label>
        <div class="input-group">
            <span class="input-group-addon"><i class="icofont icofont-ui-user"></i></span>

            <input name="date_of_employment" id="dropper-format" class="form-control" type="text" placeholder="Select your format" />
        </div>
    </div>

    <!-- employment end date -->
    <div class="col-md-3 my-1">
        <label class="block">Employment end Date</label>
        <div class="input-group">
            <span class="input-group-addon"><i class="icofont icofont-ui-user"></i></span>

            <input name="employment_end_date" id="dropper-format" class="form-control" type="text" placeholder="Select your format" />
        </div>
    </div>

    <!-- income range -->
    <div class="col-md-3 my-1">
        <label class="block">Income Range</label>
        <div class="input-group">
            <span class="input-group-addon"><i class="icofont icofont-id"></i></span>

            <select name="income_range" class="js-example-basic-single">
                <optgroup>
                    <option value="1">Below 10,000</option>
                    <option value="1">10,000 - 20,000</option>
                    <option value="2">20,000 - 30,000</option>
                </optgroup>
            </select>
        </div>
    </div>
</div>