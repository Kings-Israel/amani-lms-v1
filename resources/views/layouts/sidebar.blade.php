<nav class="pcoded-navbar">
    <div class="pcoded-inner-navbar main-menu">
        <div class="pcoded-navigatio-lavel"></div>
        <ul class="pcoded-item pcoded-left-item">
            <li class=" {{Request::path() == 'home' ? 'active' : ''}}">
                <a href="{{url('home')}}">
                    <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                    <span class="pcoded-mtext">Dashboard</span>
                </a>
            </li>

            @hasanyrole('accountant|admin|manager|agent_care|customer_informant|sector_manager')
                <li class="pcoded-hasmenu {{request()->routeIs('products.*') || request()->routeIs('payments.*') || request()->routeIs('reconcile') || request()->routeIs('reconciled_transactions') || request()->routeIs('unreconciled_transactions') || request()->routeIs('registration_transactions') || request()->routeIs('settlement_transactions') || request()->routeIs('others_transactions') ? 'active pcoded-trigger' : ''}}">
                    <a href="javascript:void(0)">
                        <span class="pcoded-micon"><i class="feather icon-sidebar"></i></span>
                        <span class="pcoded-mtext">Accounts Module</span>
                    </a>
                    <ul class="pcoded-submenu">
                        @if(!auth()->user()->hasrole('accountant') && !auth()->user()->hasRole('manager') && !auth()->user()->hasRole('agent_care'))
                            <li class="{{request()->routeIs('products.*') ? 'active' : ''}}">
                                <a href="{{route('products.index')}}">
                                    <span class="pcoded-mtext">Products</span>
                                </a>
                            </li>
                        @endif
                        <li class="pcoded-hasmenu {{ request()->routeIs('payments.*') || request()->routeIs('reconcile') || request()->routeIs('registration_transactions') || request()->routeIs('reconciled_transactions') || request()->routeIs('unreconciled_transactions')  || request()->routeIs('settlement_transactions') || request()->routeIs('others_transactions') ? 'active pcoded-trigger' : '' }}">
                            <a href="javascript:void(0)">
                                <span class="pcoded-micon"><i class="feather icon-sidebar"></i></span>
                                <span class="pcoded-mtext">Transactions</span>
                            </a>
                            <ul class="pcoded-submenu">
                                @if (!auth()->user()->hasRole('agent_care'))
                                    <li class="{{ request()->routeIs('payments.index') ? 'active' : '' }}">
                                        <a href="{{route('payments.index')}}">
                                            <span class="pcoded-mtext">Loans Transactions</span>
                                        </a>
                                    </li>
                                    <li class="{{ request()->routeIs('registration_transactions') ? 'active' : '' }}">
                                        <a href="{{route('registration_transactions')}}">
                                            <span class="pcoded-mtext">Registration Transaction</span>
                                        </a>
                                    </li>

                                    <li class="{{ request()->routeIs('settlement_transactions') ? 'active' : '' }}">
                                        <a href="{{route('settlement_transactions')}}">
                                            <span class="pcoded-mtext">Settlement Transactions</span>
                                        </a>
                                    </li>

                                    <li class="{{ request()->routeIs('others_transactions') ? 'active' : '' }}">
                                        <a href="{{route('others_transactions')}}">
                                            <!-- <span class="pcoded-mtext">Other Transactions</span> -->
                                            <span class="pcoded-mtext">Petty Cash Transactions</span>

                                        </a>
                                    </li>
                                @endif
                                <li class="{{ request()->routeIs('reconcile') ? 'active' : '' }}">
                                    <a href="{{route('reconcile')}}">
                                        <span class="pcoded-mtext">Transactions Reconcile</span>
                                    </a>
                                </li>
                                <li class="{{ request()->routeIs('reconciled_transactions') ? 'active' : '' }}">
                                    <a href="{{route('reconciled_transactions')}}">
                                        <span class="pcoded-mtext">Reconciled Transactions</span>
                                    </a>
                                </li>
                                <li class="{{ request()->routeIs('unreconciled_transactions') ? 'active' : '' }}">
                                    <a href="{{route('unreconciled_transactions')}}">
                                        <span class="pcoded-mtext">Unreconciled Payments</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="pcoded-hasmenu">
                            <a href="javascript:void(0)">
                                <span class="pcoded-micon"><i class="feather icon-sidebar"></i></span>
                                <span class="pcoded-mtext">Operations</span>
                            </a>
                            <ul class="pcoded-submenu">
                                <li class="pcoded-hasmenu">
                                    <a href="javascript:void(0)">
                                        <span class="pcoded-micon"><i class="feather icon-sidebar"></i></span>
                                        <span class="pcoded-mtext">Investors Settlement</span>
                                    </a>
                                    <ul class="pcoded-submenu">
                                        <li class="{{ request()->routeIs('investors.withdrawal') ? 'active' : '' }}">
                                            <a href="{{route('investors.withdrawal')}}">
                                                <span class="">Investors Withdrawals</span>
                                            </a>
                                        </li>
                                        <li class="{{ request()->routeIs('investors.interest') ? 'active' : '' }}">
                                            <a href="{{route('investors.interest')}}">
                                                <span class="">Investors Interest Settlement</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>

                                <li class="{{ request()->routeIs('other_settlement') ? 'active' : '' }}">
                                    <a href="{{route('other_settlement')}}">
                                        <span class="pcoded-mtext">Others</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                    </ul>
                </li>
            @endhasanyrole


            @if(!auth()->user()->hasrole('investor'))

                <li class="pcoded-hasmenu {{request()->routeIs('registry.*') || request()->routeIs('preq_amt_adjustment') ? 'active pcoded-trigger' : ''}}">
                    <a href="javascript:void(0)">
                        <span class="pcoded-micon"><i class="feather icon-layers"></i></span>
                        <span class="pcoded-mtext">Registry Module</span>
                    </a>
                    <ul class="pcoded-submenu">
                        @hasanyrole('admin|agent_care|customer_informant|sector_manager|field_agent|accountant')
                            <li class="{{ request()->routeIs('registry.create') ? 'active' : '' }}">
                                <a href="{{ route('registry.create') }}">
                                    <span class="pcoded-mtext">New customer</span>
                                </a>
                            </li>
                        @endhasanyrole
                        <li class="{{ request()->routeIs('registry.index') ? 'active' : '' }}">
                            <a href="{{ route('registry.index') }}">
                                <span class="pcoded-mtext">All customers</span>
                            </a>
                        </li>

                        <li class="{{ request()->routeIs('registry.pending') ? 'active' : '' }}">
                            <a href="{{ route('registry.pending') }}">
                                <span class="pcoded-mtext">Pending Customers</span>
                            </a>
                        </li>

                        <li class="{{ request()->routeIs('registry.blocked') ? 'active' : '' }}">
                            <a href="{{ route('registry.blocked') }}">
                                <span class="pcoded-mtext">Blocked Customers</span>
                            </a>
                        </li>

                        @role(['admin'])
                            <li class="{{ request()->routeIs('preq_amt_adjustment') ? 'active' : '' }}">
                                <a href="{{ route('preq_amt_adjustment') }}">
                                    <span class="pcoded-mtext">Loan Amount Adjustment</span>
                                </a>
                            </li>
                        @endrole

                        @hasanyrole('admin|customer_informant|sector_manager')
                            <li class="pcoded-hasmenu {{request()->routeIs('field_agent.index') || request()->routeIs('collection_officer.index') ? 'active pcoded-trigger' : ''}}">
                                <a href="javascript:void(0)">
                                    <span class="pcoded-micon"><i class="feather icon-layers"></i></span>
                                    <span class="pcoded-mtext">Relationship Officers</span>
                                </a>
                                <ul class="pcoded-submenu">
                                    <li class="{{ request()->routeIs('field_agent.index') ? 'active' : '' }}">
                                        <a href="{{route('field_agent.index')}}">
                                            <span class="pcoded-mtext">Field Agents</span>
                                        </a>
                                    </li>
                                    <li class="{{ request()->routeIs('collection_officer.index') }}">
                                        <a href="{{route('collection_officer.index')}}">
                                            <span class="pcoded-mtext">Collection Officers</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endhasanyrole

                        @hasanyrole('admin|sector_manager')
                            <li class="{{ request()->routeIs('branches.index') ? 'active' : '' }}">
                                <a href="{{route('branches.index')}}">
                                    <span class="pcoded-mtext">Branches</span>
                                </a>
                            </li>
                        @endhasanyrole
                        <!-- @hasanyrole('admin|customer_informant|field_agent|sector_manager')
                            <li class="{{ request()->routeIs('guarantors.index') ? 'active' : '' }}">
                                <a href="{{route('guarantors.index')}}">
                                    <span class="pcoded-mtext">Guarantors</span>
                                </a>
                            </li>
                        @endhasanyrole -->
                        @hasanyrole('admin|customer_informant|field_agent|sector_manager')
                            <li class="{{ request()->routeIs('referee.index') ? 'active' : '' }}">
                                <a href="{{route('referee.index')}}">
                                    <span class="pcoded-mtext"> Guarantors </span>
                                </a>
                            </li>
                        @endhasanyrole
                        @hasanyrole('admin|manager|sector_manager')
                            <li class="{{ request()->routeIs('kin.index') ? 'active' : '' }}">
                                <a href="{{route('kin.index')}}">
                                    <span class="pcoded-mtext">Kin Relationship</span>
                                </a>
                            </li>
                            <li class="{{ request()->routeIs('employers.index') ? 'active' : '' }}">
                                <a href="{{route('employers.index')}}">
                                    <span class="pcoded-mtext">Employers</span>
                                </a>
                            </li>
                        @endhasanyrole
                        <li class="{{ request()->routeIs('prospects') ? 'active' : '' }}">
                            <a href="{{route('prospects')}}">
                                <span class="pcoded-mtext">Prospects</span>
                            </a>
                        </li>
                    </ul>
                </li>
                @endhasanyrole

                <li class="pcoded-hasmenu {{request()->routeIs('pre_interactions') || request()->routeIs('pre_interactions.*') || request()->routeIs('all_interactions') ? 'active pcoded-trigger' : ''}}">
                    <a href="javascript:void(0)">
                        <span class="pcoded-micon"><i class="feather icon-list"></i></span>
                        <span class="pcoded-mtext">Interactions</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li class="{{ request()->path() == 'pre_interactions/dues' ? 'active' : '' }}">
                            <a href="{{ route('pre_interactions', ['id' => 'dues']) }}">
                                <span class="pcoded-mtext">Dues PreInteractions</span>
                            </a>
                        </li>
                        <li class="{{ request()->path() == 'pre_interactions/arrears' ? 'active' : '' }}">
                            <a href="{{ route('pre_interactions', ['id' => 'arrears']) }}">
                                <span class="pcoded-mtext">Arrears PreInteractions</span>
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('customer-interactions.select_customer') ? 'active' : '' }}">
                            <a href="{{ route('customer-interactions.select_customer') }}">
                                <span class="pcoded-mtext">Customer Interactions</span>
                            </a>
                        </li>

                        <li class="{{ request()->routeIs('all_interactions') ? 'active' : '' }}">
                            <a href="{{ route('all_interactions') }}">
                                <span class="pcoded-mtext">All Interactions</span>
                            </a>
                        </li>
                    </ul>
                </li>

            <li class="pcoded-hasmenu {{request()->routeIs('loans.*') || request()->routeIs('disbursed_loans') || request()->routeIs('loan_skipped_payments') ? 'active pcoded-trigger' : ''}}">
                <a href="javascript:void(0)">
                    <span class="pcoded-micon"><i class="feather icon-clipboard"></i></span>
                    <span class="pcoded-mtext">Loan Module</span>
                </a>
                <ul class="pcoded-submenu">

                    @hasanyrole('admin|accountant|customer_informant|field_agent')
                        <li class="{{ request()->routeIs('loans.create') ? 'active' : '' }}">
                            <a href="{{route('loans.create')}}">
                                <span class="pcoded-mtext">Create New Loan</span>
                            </a>
                        </li>
                    @endrole
                    @hasanyrole('admin|accountant|customer_informant')
                        <li class="{{ request()->routeIs('loans.index') ? 'active' : '' }}">
                            <a href="{{route('loans.index')}}">
                                <span class="pcoded-mtext">Loans</span>
                            </a>
                        </li>
                    @endrole

                    @hasanyrole('admin|accountant|customer_informant')
                        <li class="{{ request()->routeIs('loans.waitingapproval') ? 'active' : '' }}">
                            <a href="{{route('loans.waitingapproval')}}">
                                <span class="pcoded-mtext">Agents Loans </span>
                            </a>
                        </li>
                    @endrole

                    @hasanyrole('admin|accountant|customer_informant')
                        <li class="{{ request()->routeIs('loans.active') ? 'active' : '' }}">
                            <a href="{{route('loans.active')}}">
                                <span class="pcoded-mtext">Active Loans</span>
                            </a>
                        </li>
                    @endrole
                    @hasanyrole('admin|accountant|agent_care|manager|sector_manager')
                        <li class="{{ request()->routeIs('loans.restructure') ? 'active' : '' }}">
                            <a href="{{route('loans.loan_restructure')}}">
                                <span class="pcoded-mtext">Restructure Loan</span>
                            </a>
                        </li>
                    @endhasanyrole

                    @hasanyrole('manager|admin')
                        <li class="{{ request()->routeIs('loans.approval') ? 'active' : '' }}">
                            <a href="{{route('loans.approval')}}">
                                <span class="pcoded-mtext">Loan Approval</span>
                            </a>
                        </li>
                    @endhasanyrole

                    {{-- @hasanyrole('accountant|admin')
                        <li class="{{ request()->routeIs('loans.disbursement') ? 'active' : '' }}">
                            <a href="{{route('loans.disbursement')}}">
                                <span class="pcoded-mtext">Loan Disbursement</span>
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('loans.disbursement_pending') ? 'active' : '' }}">
                            <a href="{{route('loans.disbursement_pending')}}">
                                <span class="pcoded-mtext">Pending Disbursement</span>
                            </a>
                        </li>
                    @endhasanyrole --}}

                    @hasanyrole('accountant|admin|customer_informant')
                        <li class="{{ request()->routeIs('disbursed_loans') ? 'active' : '' }}">
                            <a href="{{route('disbursed_loans')}}">
                                <span class="pcoded-mtext">Disbursement History</span>
                            </a>
                        </li>
                    @endhasanyrole

                    @hasanyrole('accountant|admin|agent_care|manager|sector_manager|customer_informant')
                        <li class="{{ request()->routeIs('loan_skipped_payments') ? 'active' : '' }}">
                            <a href="{{route('loan_skipped_payments')}}">
                                <span class="pcoded-mtext">Loans With Arrears</span>
                            </a>
                        </li>
                    @endhasanyrole
                </ul>
            </li>
            @hasrole('admin|accountant|agent_care|sector_manager')
                @if(auth()->user()->hasrole('admin'))
                    <li class="pcoded-hasmenu {{request()->routeIs('admin.*') ? 'active pcoded-trigger' : ''}}">
                        <a href="javascript:void(0)">
                            <span class="pcoded-micon"><i class="feather icon-shield"></i></span>
                            <span class="pcoded-mtext">Admin Module</span>
                        </a>
                        <ul class="pcoded-submenu">
                            <li class="{{ request()->routeIs('admin.index') ? 'active' : '' }}">
                                <a href="{{route('admin.index')}}">
                                    <span class="pcoded-mtext">Users</span>
                                </a>
                            </li>
                            <li class="{{ request()->routeIs('admin.view_users_last_seen') ? 'active' : '' }}">
                                <a href="{{route('admin.view_users_last_seen')}}">
                                    <span class="pcoded-mtext">User Online Status</span>
                                </a>
                            </li>
                            <li class="{{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                                <a href="{{route('admin.settings')}}">
                                    <span class="pcoded-mtext">Settings</span>
                                </a>
                            </li>
                            <li class="pcoded-hasmenu {{ request()->routeIs('admin.customer_sms') || request()->routeIs('admin.system_sms') ? 'active pcoded-trigger' : '' }}">
                                <a href="javascript:void(0)">
                                    <span class="pcoded-micon"><i class="feather icon-sidebar"></i></span>
                                    <span class="pcoded-mtext">SMS</span>
                                </a>
                                <ul class="pcoded-submenu">
                                    <li class="{{ request()->routeIs('admin.customer_sms') ? 'active' : '' }}">
                                        <a href="{{route('admin.customer_sms')}}">
                                            <span class="pcoded-mtext">Customer SMS</span>
                                        </a>
                                    </li>
                                    <li class="{{ request()->routeIs('admin.system_sms') ? 'active' : '' }}">
                                        <a href="{{route('admin.system_sms')}}">
                                            <span class="pcoded-mtext">System SMS</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                        </ul>
                    </li>
                @endif

                <li class="pcoded-hasmenu {{ request()->routeIs('investors.*') ? 'active pcoded-trigger' : ''  }}">
                    <a href="javascript:void(0)">
                        <span class="pcoded-micon"><i class="icofont icofont-users-alt-5"></i></span>
                        <span class="pcoded-mtext">Investors Module</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li class="{{ request()->routeIs('investors.index') ? 'active' : '' }}">
                            <a href="{{route('investors.index')}}">
                                <span class="pcoded-mtext">Investors</span>
                            </a>
                        </li>

                    </ul>
                </li>

                <li class="pcoded-hasmenu {{request()->routeIs('check-off-products.*') || request()->routeIs('check-off.loans.*') || request()->routeIs('check-off-employees.*') || request()->routeIs('check-off-loans.*') ? 'active pcoded-trigger' : ''}}">
                    <a href="javascript:void(0)">
                        <span class="pcoded-micon"><i class="icofont icofont-user-suited"></i></span>
                        <span class="pcoded-mtext">CheckOff Module</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li class="{{ request()->routeIs('check-off-products.index') ? 'active' : '' }}">
                            <a href="{{route('check-off-products.index')}}">
                                <span class="pcoded-mtext">Products</span>
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('check-off-employers.index') ? 'active' : '' }}">
                            <a href="{{route('check-off-employers.index')}}">
                                <span class="pcoded-mtext">Employers</span>
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('check-off-employees.index') ? 'active' : '' }}">
                            <a href="{{route('check-off-employees.index')}}">
                                <span class="pcoded-mtext">Employees</span>
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('check-off-loans.index') ? 'active' : '' }}">
                            <a href="{{route('check-off-loans.index')}}">
                                <span class="pcoded-mtext">Loans</span>
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('check-off-loans-disbursement.index') ? 'active' : '' }}">
                            <a href="{{route('check-off-loans-disbursement.index')}}">
                                <span class="pcoded-mtext">Loan Disbursement</span>
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('check-off.loans.index') ? 'active' : '' }}">
                            <a href="{{route('check-off.loans.payment_index')}}">
                                <span class="pcoded-mtext">Payments</span>
                            </a>
                        </li>
                    </ul>
                </li>
            @endrole

            @hasrole('admin|accountant|agent_care|collection_officer|sector_manager|manager')
                <li class="pcoded-hasmenu {{ request()->routeIs('reports') || request()->routeIs('reports.*') ? 'active pcoded-trigger' : '' }}">
                    <a href="javascript:void(0)">
                        <span class="pcoded-micon"><i class="feather icon-edit"></i></span>
                        <span class="pcoded-mtext">Reports Module</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li class="{{ request()->routeIs('reports') || request()->routeIs('reports.*') ? 'active' : '' }}">
                            <a href="{{ route('reports') }}">
                                <span class="pcoded-mtext">Reports</span>
                            </a>
                        </li>
                    </ul>
                </li>
            @endhasrole
        </ul>
    </div>
</nav>
