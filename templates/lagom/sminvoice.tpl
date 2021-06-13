{if $fatalError}
        {include file="$template/includes/message.tpl" type="danger" customClass="message-no-data" msg=$fatalErrorMessage}
{else}
    {if $normalError}
        {include file="$template/includes/alert.tpl" type="danger" msg=$normalErrorMessage}
    {/if}
    {if $invoiceError}
        {include file="$template/includes/alert.tpl" type="danger" msg=$LANG.invoiceserror}
        {include file="$template/includes/alert.tpl" type="success" msg=$LANG.smpaypal.success}
        {include file="$template/includes/alert.tpl" type="danger" msg=$LANG.invoicepaymentfailedconfirmation}
    {/if}
    <div class="row row-eq-height row-eq-height-md ">
        <div class="col-md-12 tab-m-b-48">
            <div class="invoice">
                {if $paymentSuccessAwaitingNotification}
                    {include file="$template/includes/alert.tpl" type="success" msg=$LANG.invoicePaymentSuccessAwaitingNotify}
                {elseif $paymentSuccess}
                    {include file="$template/includes/alert.tpl" type="success" msg=$LANG.invoicepaymentsuccessconfirmation}
                {elseif $paymentInititated}
                    {include file="$template/includes/alert.tpl" type="info" msg=$LANG.invoicePaymentInitiated}
                {elseif $pendingReview}
                    {include file="$template/includes/alert.tpl" type="info" msg=$LANG.invoicepaymentpendingreview}
                {elseif $paymentFailed}
                    {include file="$template/includes/alert.tpl" type="danger" msg=$LANG.invoicepaymentfailedconfirmation}
                {elseif $offlineReview}
                    {include file="$template/includes/alert.tpl" type="info" msg=$LANG.invoiceofflinepaid}
                {/if}
                <div class="section">
                    <div class="row">
                        <div class="col-sm-7">
                            <span class="invoice-title"> {$invoiceTitle}
                                {if $status eq "Draft"}
                                    <span class="invoice-status label label-lg label-info">{$LANG.invoicesdraft}</span>                                    
                                {elseif $status eq "Unpaid"}
                                    <span class="invoice-status label label-lg label-danger">{$LANG.invoicesunpaid}</span>
                                {elseif $status eq "Paid"}
                                    <span class="invoice-status label label-lg label-success">{$LANG.invoicespaid}</span>                                   
                                {elseif $status eq "Refunded"}
                                    <span class="invoice-status label label-lg label-info">{$LANG.invoicesrefunded}</span>                                          
                                {elseif $status eq "Cancelled"}
                                    <span class="invoice-status label label-lg label-info">{$LANG.invoicescancelled}</span>         
                                {elseif $status eq "Collections"}
                                    <span class="invoice-status label label-lg label-info">{$LANG.invoicescollections}</span>         
                                {elseif $status eq "Payment Pending"}
                                    <span class="invoice-status label label-lg label-warning">{$LANG.invoicesPaymentPending}</span>          
                                {/if}
                            </span>
                        </div>
                        <div class="col-sm-5">
                            <ul class="list-info list-info-50">
                                <li>
                                    <span class="list-info-text">{$LANG.invoicesdatecreated}</span>
                                    <span class="list-info-title">{$date}</span>
                                </li>
                                {if $status eq "Unpaid" || $status eq "Draft"}
                                    <li>
                                        <span class="list-info-text">{$LANG.invoicesdatedue}</span>
                                        <span class="list-info-title">{$datedue}</span>
                                    </li>
                                {/if}
                                {if $status neq "Unpaid"}
                                    <li>
                                        <span class="list-info-text">{$LANG.orderpaymentmethod}</span>
                                        <span class="list-info-title">{$paymentmethod}{if $paymethoddisplayname} ({$paymethoddisplayname}){/if}</span>
                                    </li>
                                {/if}
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="section">
                    <div class="row">
                        <div class="col-sm-7">
                            <h3>{$LANG.invoicespayto}:</h3>
                            <address>
                                {$LANG.smpaypal.sendPaymentTo}
                                <br />
                                {$payto}
                                {if $taxCode}<br />{$taxIdLabel}: {$taxCode}{/if}
                            </address>
                        </div>
                        <div class="col-sm-5">
                            <h3>{$LANG.invoicesinvoicedto}:</h3>
                            <address> {if $clientsdetails.companyname}{$clientsdetails.companyname}<br />{/if}
                                    {$clientsdetails.firstname} {$clientsdetails.lastname}<br />
                                    {$clientsdetails.address1}, {$clientsdetails.address2}<br />
                                    {$clientsdetails.city}, {$clientsdetails.state}, {$clientsdetails.postcode}<br />
                                    {$clientsdetails.country}
                                    {if $clientsdetails.tax_id}<br />{$taxIdLabel}: {$clientsdetails.tax_id}{/if}
                                    {if $customfields}
                                    <br /><br />
                                    {foreach from=$customfields item=customfield}
                                    {$customfield.fieldname}: {$customfield.value}<br />
                                    {/foreach}
                                    {/if}
                            </address>
                        </div>
                    </div>
                </div>
                <div class="section">
                    <h3>{$LANG.invoicelineitems}</h3>
                    <div class="table-responsive">
                        <table class="table table-condensed">
                            <thead>
                                <tr>
                                    <th width="61%">{$LANG.invoicesdescription}</th>
                                    <th width="20%"></th>
                                    <th width="19%" class="text-center">{$LANG.invoicesamount}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {foreach from=$invoiceitems item=item}
                                    <tr>
                                        <td colspan="2">{$item.description}{if $item.taxed eq "true"} *{/if}</td>
                                        <td class="text-center">{$item.amount}</td>
                                    </tr>
                                {/foreach}
                                <tr class="sub-total-row first">
                                    <td></td>
                                    <td>{$LANG.invoicessubtotal}</td>
                                    <td>{$subtotal}</td>
                                </tr>
                                {if $taxname}
                                    <tr class="sub-total-row">
                                        <td></td>
                                        <td>{$taxrate}% {$taxname}</td>
                                        <td>{$tax}</td>
                                    </tr>
                                {/if}
                                {if $taxname2}
                                    <tr class="sub-total-row">
                                        <td></td>
                                        <td>{$taxrate2}% {$taxname2}</td>
                                        <td>{$tax2}</td>
                                    </tr>
                                {/if}
                                <tr class="sub-total-row last">
                                    <td></td>
                                    <td>{$LANG.invoicescredit}</td>
                                    <td>{$credit}</td>
                                </tr>
                                <tr class="total-row">
                                    <td></td>
                                    <td class="h3">{$LANG.invoicestotal}</td>
                                    <td class="h3">{$total}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    {if $taxrate}
                        <p>* {$LANG.invoicestaxindicator}</p>
                    {/if}
                </div>
    
    
                                
                {if $notes}
                <div class="notes">
                        <div class="notes-heading">
                            <h3 class="notes-title"><strong>{$LANG.invoicesnotes}</strong></h3>
                        </div>
                    <div class="well notes-body">
                        {$notes}
                    </div>
                </div>      
                {/if}
            </div>    
        </div>
                                                                                        
    </div>
    
    {if $status eq "Unpaid"}
        <div class="panel panel-default pnale-form well-lg m-b-6 m-t-16">
            <p style="margin: -28px -17px 8px -28px;font-size: 20px;">{$LANG.smpaypal.submitTransaction}</p>
            <div class="panel-body">
            <form method="GET" action="smverify.php" class="m-w-416 m-a m-t-neg-6">
                <input type="hidden" name="invoiceID" value={$invoiceID}>
                <div class="form-group">
                    <label for="paymentmethod" class="control-label">{$LANG.smpaypal.transactionID}:</label>
                </div>
                <div class="form-group m-b-0">
                    <div class="input-group">
                        <input type="text" name="transactionID" value="" class="form-control" required />
                        <span class="input-group-btn">
                            <button type="submit" value="{$LANG.smpaypal.registerTransaction}" class="btn btn-primary text-center" data-btn-loader>
                                <span>{$LANG.smpaypal.registerTransaction}</span>
                                <div class="loader loader-button hidden" >
                                    {include file="$template/includes/loader.tpl" classes="spinner-sm spinner-light"}  
                                </div>
                            </button>
                        </span>
                    </div>
                </div>    
            </form>
            </div>
        </div>
    {/if}
{/if}
