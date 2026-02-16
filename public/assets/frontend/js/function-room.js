$(document).ready(function () {
    flatpickr("#functionRoomBookingDate", {
        dateFormat: "Y-m-d",
        minDate: new Date().fp_incr(6)
    });

    $("#functionRoomBookingModal").on("show.bs.modal", function () {

        $(".addonQty").each(function () {
            $(this).prop("disabled", true).val(0);
        });

        $(".text-muted[id^='addonAvailable']").text("Available: -");
    });

    $(document).on("change", ".addOnsFields", function () {
        let addonId = $(this).attr("id").replace("addon", "");
        let qtyInput = $("input[data-addon-id='" + addonId + "']");

        if ($(this).is(":checked")) {
            qtyInput.prop("disabled", false);
            qtyInput.prop("required", true);

            if (parseInt(qtyInput.val()) === 0) {
                qtyInput.val(1);
            }
        } else {
            qtyInput.prop("disabled", true);
            qtyInput.prop("required", false).val(0);
        }
    });

    $(document).on("input", ".addonQty", function () {
        let max = parseInt($(this).attr("max")) || 0;
        let val = parseInt($(this).val()) || 0;

        if (val > max) {
            $(this).val(max);
        } else if (val < 0) {
            $(this).val(0);
        }
    });

    $('.BookFunctionRoomBtn').on('click', function () {

        showLoading();

        var functionRoomName = $(this).data("name");
        var roomId = $(this).data("id");
        var linkedRooms = $(this).data("linked"); // this will be an array of names

        if (linkedRooms && linkedRooms.length > 0) {
            $("#linkedRoomContainer").html(`
        <div class="col-md-12 mt-2">
            <div class="form-check">
                <input type="checkbox" name="book_linked_rooms" id="bookLinkedRooms" value="1" class="form-check-input">
                <label class="form-check-label fw-semibold" for="bookLinkedRooms">
                    You can also book: <span class="text-primary">${linkedRooms.join(', ')}</span>
                </label>
                <i class="bi bi-question-circle ms-1 text-secondary" data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        title="If you book both function rooms, you will also get the space between them."></i>
            </div>
            <input type="hidden" name="linked_room_ids" value="${linkedRooms.join(',')}">
        </div>
        
        `);
        } else {
            $("#linkedRoomContainer").html("");
        }

        $.ajax({
            url: "/check-auth",
            method: "GET",
            success: function (response) {
                if (response.authenticated) {
                    $.ajax({
                        url: `/function-room/${roomId}/booked-dates`,
                        type: 'GET',
                        success: function (disabledDates) {
                            let dateInput = document.getElementById("functionRoomBookingDate");
                            if (dateInput._flatpickr) dateInput._flatpickr.destroy();

                            flatpickr("#functionRoomBookingDate", {
                                dateFormat: "Y-m-d",
                                minDate: new Date().fp_incr(6),
                                disable: disabledDates,
                                onChange: function (selectedDates, dateStr) {
                                    if (!dateStr) return;

                                    $.ajax({
                                        url: '/function-room/addons-availability',
                                        type: 'GET',
                                        data: {
                                            date: dateStr,
                                            room_id: $("#userFunctionRoomNewBooking input[name=function_room_id]").val()
                                        },
                                        success: function (res) {
                                            $(".addonQty").each(function () {
                                                let addonId = $(this).data("addon-id");
                                                let available = res[addonId] ?? 0;
                                                let checkbox = $("#addon" + addonId);

                                                // Update available text
                                                $("#addonAvailable" + addonId).text("Available: " + available);

                                                if (available <= 0) {
                                                    // Disable everything if no stock
                                                    $(this).prop("disabled", true).val(0).prop("required", false);
                                                    checkbox.prop("disabled", true).prop("checked", false);
                                                } else {
                                                    // Enable checkbox, but keep qty disabled until checked
                                                    $(this).attr("max", available).val(0).prop("disabled", true).prop("required", false);
                                                    checkbox.prop("disabled", false).prop("checked", false);
                                                }
                                            });
                                        }

                                    });
                                }
                            });



                            $("#functionRoomBookingModal").modal("show");

                        },
                        error: function () {
                            alert("Failed to load booked dates.");
                        },
                        complete: function () {
                            hideLoading();
                        }
                    });
                } else {
                    let redirectUrl = encodeURIComponent(window.location.href);
                    window.location.href = "/login?redirect=" + redirectUrl;
                    hideLoading();
                }
            },
            error: function () {
                alert("Something went wrong. Please try again.");
                $("#loadingOverlay").fadeOut();
            }
        });
    });



    let supplierIndex = 1;

    $('#hasSuppliers').on('change', function () {
        if ($(this).is(':checked')) {
            $('#supplierSection').removeClass('d-none');

            // if empty, add back the first supplier row
            if ($('#suppliersWrapper').children().length === 0) {
                let firstRow = `
            <div class="row g-2 supplier-item mb-2">
                <div class="col-md-4">
                    <input type="text" name="suppliers[0][name]" class="form-control" placeholder="Supplier Name">
                </div>
                <div class="col-md-4">
                    <input type="file" name="suppliers[0][attachment]" class="form-control" accept="image/*,.pdf">
                </div>
            </div>`;
                $('#suppliersWrapper').html(firstRow);
                supplierIndex = 1;
            }

        } else {
            $('#supplierSection').addClass('d-none');
            $('#suppliersWrapper').html('');
            supplierIndex = 1;
        }
    });

    $('#addSupplier').on('click', function () {
        let newRow = `
        <div class="row g-2 supplier-item mb-2">
            <div class="col-md-4">
                <input type="text" name="suppliers[${supplierIndex}][name]" class="form-control" placeholder="Name">
            </div>
            <div class="col-md-6">
                <input type="file" name="suppliers[${supplierIndex}][attachment]" class="form-control" accept="image/*,.pdf">
            </div>
            <div class="col-md-2 d-flex align-items-center">
                <button type="button" class="btn btn-sm btn-danger removeSupplier customBtn">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </div>`;
        $('#suppliersWrapper').append(newRow);
        supplierIndex++;
    });

    $(document).on('click', '.removeSupplier', function () {
        $(this).closest('.supplier-item').remove();
    });


    $('#paxInput').on('input', function () {
        let pax = parseInt($(this).val());
        let max = parseInt($('#roomCapacity').val());
        if (pax > max) {
            $('#capacityError').removeClass('d-none');
            $(this).val(max);
        } else {
            $('#capacityError').addClass('d-none');
        }
    });

    $('#residentSelect').on('change', function () {
        // Uncheck radios
        $('input[name="payment_mode"]').prop('checked', false);

        hideAuthorization();
    });

    $('input[name="payment_mode"]').on('change', checkAuthorization);

    function hideAuthorization() {
        const $wrapper = $('#authorizationUploadWrapper');
        const $fileInput = $('input[name="authorization_file"]');

        $wrapper.addClass('d-none');
        $('#authorizationLabel').text('');
        $('#authorizationNote').text('');
        $fileInput.prop('required', false);
        $fileInput.val('');
    }

    function checkAuthorization() {
        let selectedOption = $('#residentSelect option:selected');
        let residentType = (selectedOption.data('type') || '').toString().trim().toLowerCase();
        let unitNo = (selectedOption.data('unit') || '').toString().trim();
        let paymentMode = ($('input[name="payment_mode"]:checked').val() || '').toString().trim();

        const $wrapper = $('#authorizationUploadWrapper');
        const $fileInput = $('input[name="authorization_file"]');

        hideAuthorization();
        if (!residentType || !paymentMode) return;
        if (paymentMode !== 'Charge to Account') return;
        if (residentType === 'tenant') {
            $('#authorizationLabel').text('CTA Authorization Letter *');
            $('#authorizationNote').text('Required because you are booking as a tenant with CTA.');
            $wrapper.removeClass('d-none');
            $fileInput.prop('required', true);
            return;
        }

        if (residentType === 'owner' && unitNo) {
            $.get('/check-unit-tenant/' + encodeURIComponent(unitNo))
                .done(function (response) {
                    if (response && response.hasTenant) {
                        $('#authorizationLabel').text('Tenant Authorization Letter *');
                        $('#authorizationNote').text('Required because the unit is tenanted.');
                        $wrapper.removeClass('d-none');
                        $fileInput.prop('required', true);
                    } else {
                        hideAuthorization();
                    }
                })
                .fail(function () {
                    console.error('Failed to check unit tenancy.');
                    hideAuthorization();
                });
        }
    }



    $('#userFunctionRoomNewBooking').submit(function (event) {
        event.preventDefault();

        const form = this;
        let isValid = true;
        const startTime = $('#startTime').val();
        const endTime = $('#endTime').val();

        function convertToMinutes(time) {
            if (!time) return null;
            const [hours, minutes] = time.split(':').map(Number);
            return hours * 60 + minutes;
        }

        const startMinutes = convertToMinutes(startTime);
        const endMinutes = convertToMinutes(endTime);

        if (startMinutes !== null && endMinutes !== null && endMinutes <= startMinutes) {
            $('#timeError').removeClass('d-none');
            $('#endTime').addClass('is-invalid');
            isValid = false;
        } else {
            $('#timeError').addClass('d-none');
            $('#endTime').removeClass('is-invalid');
        }

        if (!isValid) return;

        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }
        form.classList.remove('was-validated');

        const $btn = $('#saveUserFunctionRoomBtn');
        const originalWidth = $btn.outerWidth();
        $btn
            .attr('disabled', true)
            .html(`<div class="spinner-border spinner-border-sm text-light"></div>`)
            .css('width', originalWidth + 'px');

        const formData = new FormData(form);

        $.ajax({
            url: $(form).attr('action'),
            type: $(form).attr('method'),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                $('#functionRoomBookingModal').modal('hide');

                Swal.fire({
                    icon: 'success',
                    title: 'Booking Submitted!',
                    text: response.message || 'Your booking has been successfully submitted.',
                    timer: 2000,
                    showConfirmButton: false
                });

                form.reset();
                $(form).removeClass('was-validated');
                $(form).find('#supplierSection, #authorizationUploadWrapper').hide();
            },
            error: function (xhr) {
                // 409 → Conflict (double booking OR date blocked)
                if (xhr.status === 409) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Booking Conflict',
                        text: xhr.responseJSON.message,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#d33'
                    });
                    return;
                }

                // 422 → Add-ons stock insufficient
                if (xhr.status === 422) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Not Enough Add-ons',
                        text: xhr.responseJSON.message,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#d33'
                    });
                    return;
                }

                // Fallback error
                Swal.fire({
                    toast: true,
                    position: "top-end",
                    icon: 'error',
                    title: 'Something went wrong. Please try again later.',
                    timer: 3000,
                    showConfirmButton: false
                });
            },
            complete: function () {
                $btn
                    .attr('disabled', false)
                    .html(`<span class="btn-text">Submit</span>`)
                    .css('width', '');
            }
        });
    });


    $('input[name="contact_number"]').on('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 11);
    })


    window.showLoading = function () {
        $('#loadingOverlay').css('display', 'flex').hide().fadeIn(150);
    }

    window.hideLoading = function () {
        $('#loadingOverlay').fadeOut(150);
    }
    $(document).on('click', '.function-room-booking-details', function () {
        const bookingId = $(this).data('id');
        showLoading();
        function parseTimeToDate(timeStr) {
            if (!timeStr) return null;
            timeStr = String(timeStr).trim();
            const ampmMatch = timeStr.match(/(\d{1,2}):(\d{2})(?::\d{2})?\s*([AaPp][Mm])/);
            if (ampmMatch) {
                let h = parseInt(ampmMatch[1], 10);
                const m = parseInt(ampmMatch[2], 10);
                const ampm = ampmMatch[3].toUpperCase();
                if (ampm === 'PM' && h !== 12) h += 12;
                if (ampm === 'AM' && h === 12) h = 0;
                return new Date(1970, 0, 1, h, m, 0);
            }
            const twentyFour = timeStr.match(/(\d{1,2}):(\d{2})(?::(\d{2}))?/);
            if (twentyFour) {
                const h = parseInt(twentyFour[1], 10);
                const m = parseInt(twentyFour[2], 10);
                const s = twentyFour[3] ? parseInt(twentyFour[3], 10) : 0;
                return new Date(1970, 0, 1, h, m, s);
            }
            const d = new Date(`1970-01-01T${timeStr}`);
            return isNaN(d.getTime()) ? null : d;
        }

        function formatTimeStr(timeStr) {
            const d = parseTimeToDate(timeStr);
            if (!d) return (timeStr ?? 'N/A');
            let h = d.getHours();
            const m = d.getMinutes();
            const ampm = h >= 12 ? 'PM' : 'AM';
            h = h % 12;
            if (h === 0) h = 12;
            return `${h}:${String(m).padStart(2, '0')} ${ampm}`;
        }

        function computeHours(startStr, endStr) {
            const s = parseTimeToDate(startStr);
            const e = parseTimeToDate(endStr);
            if (!s || !e) return 1;
            const start = new Date(s.getTime());
            const end = new Date(e.getTime());
            if (end <= start) {
                end.setDate(end.getDate() + 1);
            }
            const diffMinutes = (end - start) / (1000 * 60);
            let hours = Math.round((diffMinutes / 60) * 100) / 100; // 2 decimals
            if (!isFinite(hours) || hours <= 0) hours = 1;
            return hours;
        }

        function currency(num) {
            return Number(num || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        $.get(`/view-function-room-bookings/${bookingId}/details`, function (response) {
            if (!response.success) {
                hideLoading();
                return alert('Failed to load booking details.');
            }
            const main = response.booking;

            const linked = Array.isArray(response.linked_bookings) && response.linked_bookings.length ? response.linked_bookings : [];
            $('#detail-transaction-no').text(main.transaction_no ?? 'N/A');
            $('#detail-unit').text(main.unit_no ?? 'N/A');
            if (main.user?.name) {
                $('#detail-name').text(main.user.name);
            } else if (main.created_by_name) {
                $('#detail-name').text('Booked by: ' + main.created_by_name);
            } else {
                $('#detail-name').text('N/A');
            }
            $('#detail-contact').text(main.contact_number ?? 'N/A');
            $('#detail-purpose').text(main.purpose_of_event ?? 'N/A');

            const residentBadge = main.resident_type === 'TENANT'
                ? '<span class="badge badge-forge bg-danger">TENANT</span>'
                : main.resident_type === 'OWNER'
                    ? '<span class="badge badge-forge bg-primary">OWNER</span>'
                    : `<span class="badge badge-forge bg-secondary">${main.resident_type ?? 'N/A'}</span>`;
            $('#detail-resident-type').html(residentBadge);
            $('#detail-payment-mode').text(main.payment_mode ?? 'N/A');


            let statusHtml = '';
            if (linked.length) {
                const allConfirmed = linked.every(b => b.booking_status == 1);
                const allCancelled = linked.every(b => b.booking_status == 2);
                if (allConfirmed) statusHtml = '<span class="badge badge-forge bg-success">Confirmed</span>';
                else if (allCancelled) statusHtml = '<span class="badge badge-forge bg-danger">Cancelled</span>';
                else statusHtml = '<span class="badge badge-forge bg-warning text-light">Waiting</span>';
            } else {
                if (main.booking_status == 1) statusHtml = '<span class="badge badge-forge bg-success">Confirmed</span>';
                else if (main.booking_status == 2) statusHtml = '<span class="badge badge-forge bg-danger">Cancelled</span>';
                else statusHtml = '<span class="badge badge-forge bg-warning text-light">Waiting</span>';
            }
            $('#detail-status').html(statusHtml);

            const today = new Date(); today.setHours(0, 0, 0, 0);
            const mainBookingDate = main.function_room_booking_date ? new Date(main.function_room_booking_date) : null;
            if (main.booking_status == 1) {
                if (mainBookingDate && mainBookingDate < today) {
                    $('#cancel-booking-btn').addClass('d-none');
                } else {
                    $('#cancel-booking-btn').removeClass('d-none').data('id', main.id).data('start-time', main.event_start_time);
                }
            } else if (main.booking_status == 0) {
                $('#cancel-booking-btn').removeClass('d-none').data('id', main.id).data('start-time', main.event_start_time);
            } else {
                $('#cancel-booking-btn').addClass('d-none');
            }

            if (linked.length) {
                let roomsHtml = '';
                linked.forEach(b => {
                    const name = (b.function_room && b.function_room.function_room_name) || (b.functionRoom && b.functionRoom.function_room_name) || 'Function Room';
                    roomsHtml += `<span class="badge badge-forge bg-primary me-1">${name}</span>`;
                });
                $('#detail-function-rooms').html(roomsHtml);
            } else {
                const name = (main.function_room && main.function_room.function_room_name) || (main.functionRoom && main.functionRoom.function_room_name) || 'N/A';
                $('#detail-function-rooms').html(`<span class="badge badge-forge bg-primary">${name}</span>`);
            }

            if (response.authorization_file_url) {
                $('#detail-authorization').html(`<a href="${response.authorization_file_url}" target="_blank" class="text-decoration-none">View</a>`);
            } else {
                $('#detail-authorization').html('<span class="text-muted">N/A</span>');
            }

            if (main.suppliers && main.suppliers.length) {
                let supHtml = '';
                main.suppliers.forEach(s => {
                    supHtml += `<div>${s.name} ${s.attachment_url ? ` - <a href="${s.attachment_url}" target="_blank" class="text-decoration-none">View</a>` : ''}</div>`;
                });
                $('#detail-suppliers').html(supHtml);
            } else {
                $('#detail-suppliers').html('<span class="text-muted">N/A</span>');
            }

            const roomsToRender = linked.length ? linked : [main];
            let roomListHtml = '';
            roomsToRender.forEach(b => {
                const fr = b.function_room || b.functionRoom || {};
                const roomName = fr.function_room_name || fr.name || 'Function Room';
                const startRaw = b.event_start_time;
                const endRaw = b.event_end_time;
                const hours = computeHours(startRaw, endRaw);
                const ratePerHour = parseFloat(b.final_rate ?? fr.function_room_rate ?? fr.rate ?? 0) || 0;
                const baseRate = parseFloat(fr.function_room_rate ?? ratePerHour) || ratePerHour;
                const roomTotal = Math.round(hours * ratePerHour * 100) / 100;

                const discountValue = parseFloat(b.discount ?? 0) || 0;
                const discountRemarks = b.discount_remarks ?? '';
                const startFmt = startRaw ? formatTimeStr(startRaw) : 'N/A';
                const endFmt = endRaw ? formatTimeStr(endRaw) : 'N/A';
                const bookingDateFmt = b.function_room_booking_date ? new Date(b.function_room_booking_date).toLocaleDateString(undefined, { month: 'long', day: '2-digit', year: 'numeric' }) : 'N/A';

                roomListHtml += `
                <div class="border-bottom pb-2 mb-3">
                    <h6 class="fw-bold">${roomName}</h6>

                    <div class="row mb-2">
                        <div class="col-4 fw-bold">Booking Date:</div>
                        <div class="col-8">${bookingDateFmt}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 fw-bold">Time:</div>
                        <div class="col-8">${startFmt} - ${endFmt}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 fw-bold">Pax:</div>
                        <div class="col-8">${b.pax ?? 'N/A'}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4 fw-bold">Rate:</div>
                        <div class="col-8">`;

                if (baseRate > ratePerHour) {
                    roomListHtml += `<small class="text-muted"><s>₱${currency(baseRate)}/hr</s></small>
                    &nbsp; → &nbsp;
                    <small class="fw-bold">₱${currency(ratePerHour)}/hr</small>
                    &nbsp; × &nbsp;
                    <small>${hours} hr${hours > 1 ? 's' : ''}</small>
                    &nbsp; = &nbsp;
                    <strong>₱${currency(roomTotal)}</strong>`;
                } else {
                    roomListHtml += `<small>₱${currency(ratePerHour)}/hr</small>
                    &nbsp; × &nbsp;
                    <small>${hours} hr${hours > 1 ? 's' : ''}</small>
                    &nbsp; = &nbsp;
                    <strong>₱${currency(roomTotal)}</strong>`;
                }

                roomListHtml += `</div></div>`;
                roomListHtml += `<div class="row mb-2"><div class="col-4 fw-bold">Discount:</div><div class="col-8">`;
                if (discountValue > 0) {
                    const dvStr = Number.isInteger(discountValue) ? discountValue.toFixed(0) : (discountValue % 1 === 0 ? discountValue.toFixed(0) : discountValue.toFixed(2).replace(/\.00$/, ''));
                    roomListHtml += `<strong class="text-danger">${dvStr}%</strong>`;
                    if (discountRemarks) roomListHtml += ` <span style="margin-left:6px;color:#555;">${discountRemarks}</span>`;
                } else {
                    roomListHtml += `<span class="text-muted">No discount</span>`;
                }
                roomListHtml += `</div></div></div>`;
            });

            $('#detail-room-list').html(roomListHtml);

            let functionRoomsTotal = 0;
            let addonsTotal = 0;
            let breakdownRows = '';


            roomsToRender.forEach(b => {
                const fr = b.function_room || b.functionRoom || {};
                const roomName = fr.function_room_name || fr.name || 'Function Room';
                const hours = computeHours(b.event_start_time, b.event_end_time);
                const ratePerHour = parseFloat(b.final_rate ?? fr.function_room_rate ?? fr.rate ?? 0) || 0;
                const baseRate = parseFloat(fr.function_room_rate ?? ratePerHour) || ratePerHour;
                const roomTotal = Math.round(hours * ratePerHour * 100) / 100;
                functionRoomsTotal += roomTotal;

                breakdownRows += `
                <tr>
                    <td>${roomName}</td>
                    <td class="text-center">${hours} hr${hours > 1 ? 's' : ''}</td>
                    <td class="text-end">`;
                if (baseRate > ratePerHour) {
                    breakdownRows += `<small class="text-muted"><s>₱${currency(baseRate)}</s></small>&nbsp;→&nbsp;<small class="fw-bold">₱${currency(ratePerHour)}</small>`;
                } else {
                    breakdownRows += `₱${currency(ratePerHour)}`;
                }
                breakdownRows += `</td><td class="text-end">₱${currency(roomTotal)}</td></tr>`;
            });

            roomsToRender.forEach(b => {
                const addOns = b.addOns || b.add_ons || [];
                addOns.forEach(addon => {
                    const pivot = addon.pivot || {};
                    const qty = Number(pivot.quantity ?? pivot.qty ?? addon.qty ?? 0) || 0;
                    const price = Number(pivot.price ?? addon.price ?? addon.price ?? 0) || 0;
                    const lineTotal = Math.round(qty * price * 100) / 100;
                    addonsTotal += lineTotal;

                    breakdownRows += `
                    <tr>
                        <td>${addon.item ?? addon.name ?? 'Add-on'}</td>
                        <td class="text-center">${qty}</td>
                        <td class="text-end">₱${currency(price)}</td>
                        <td class="text-end">₱${currency(lineTotal)}</td>
                    </tr>
                `;
                });
            });

            if (!breakdownRows) {
                breakdownRows = `<tr><td colspan="4" class="text-center text-muted">No charges</td></tr>`;
            }

            $('#detail-breakdown').html(breakdownRows);
            const functionRoomsSubtotalStr = currency(functionRoomsTotal);
            const addonsTotalStr = currency(addonsTotal);
            const grandTotalStr = currency(Math.round((functionRoomsTotal + addonsTotal) * 100) / 100);

            $('#detail-breakdown-footer').html(`
            <tr class="table-light fw-bold">
                <td colspan="3" class="text-end">Function Rooms Subtotal</td>
                <td class="text-end">₱${functionRoomsSubtotalStr}</td>
            </tr>
            ${addonsTotal > 0 ? `
            <tr class="table-light fw-bold">
                <td colspan="3" class="text-end">Add-Ons Subtotal</td>
                <td class="text-end">₱${addonsTotalStr}</td>
            </tr>` : ''}
            <tr class="table-dark fw-bold">
                <td colspan="3" class="text-end">Grand Total</td>
                <td class="text-end">₱${grandTotalStr}</td>
            </tr>
        `);

            hideLoading();
            $('#userViewfunctionRoomBookingDetailsModal').modal('show');

        }).fail(function () {
            hideLoading();
            alert('Something went wrong.');
        });
    });



    $('#cancel-booking-btn').on('click', function () {
        const bookingId = $(this).data('id');
        const startTime = $(this).data('start-time');

        const bookingStart = new Date(startTime);
        const now = new Date();
        const hoursDiff = (bookingStart - now) / (1000 * 60 * 60);

        let swalText = "Are you sure you want to cancel this booking?";
        if (hoursDiff < 24) {
            swalText = "⚠️ This booking starts in less than 24 hours. Cancelling will incur a ₱1000 penalty. Continue?";
        }

        Swal.fire({
            title: 'Cancel Booking',
            text: swalText,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, cancel it',
            cancelButtonText: 'No, keep it'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/booking/' + bookingId + '/cancel',
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (res) {
                        $("#userViewfunctionRoomBookingDetailsModal").modal("hide");
                        if (res.success) {
                            Swal.fire('Cancelled!', 'The booking has been cancelled.', 'success')
                                .then(() => {
                                    // reload current filtered table
                                    let page = $('.pagination .active span').text() || 1;
                                    loadBookings(page);
                                });
                        } else {
                            Swal.fire('Error', res.message || 'Failed to cancel booking.', 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error', 'Something went wrong while cancelling.', 'error');
                    }
                });
            }
        });
    });
});


