                            <td>
                                <a href="index.php?module=patient&action=view_medicine_order&id=<?php echo $order['id']; ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if (isset($order['payment_status']) && $order['payment_status'] == 'pending'): ?>
                                    <a href="index.php?module=patient&action=payment&type=medicine&id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-credit-card"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if ($order['status'] !== 'cancelled' && $order['status'] !== 'completed'): ?>
                                    <a href="index.php?module=patient&action=cancel_medicine_order&id=<?php echo $order['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to cancel this medicine order?');">
                                        <i class="fas fa-times"></i>
                                    </a>
                                <?php endif; ?>
                            </td> 