import React, { PropsWithChildren, useEffect, useRef, useState } from 'react';
import styles from './mobile-dialog.module.scss';
import { useTrans } from '@/app/i18n/ClientTranslationProvider';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faXmark } from '@fortawesome/free-solid-svg-icons';
import { Button, Tooltip } from '@digdir/designsystemet-react';

interface DialogProps extends PropsWithChildren {
    /** Boolean to control the visibility of the modal */
    open: boolean;
    /** Function to close the modal */
    onClose: () => void;
    /** Boolean to control whether the default header is shown */
    showDefaultHeader?: boolean;
    /** Size of the dialog */
    size?: 'hd';
    /** Whether to confirm on close */
    confirmOnClose?: boolean;
}

/**
 * Dialog Component
 *
 * This component renders a modal that is fullscreen on mobile and windowed on desktop.
 * It uses the `<dialog>` HTML element and SCSS modules for styling.
 *
 * @param open - Controls whether the modal is open or closed
 * @param onClose - Callback function to close the modal
 * @param showDefaultHeader - Controls whether the default header is shown (default: true)
 * @param confirmOnClose - Prompts the user for confirmation before closing (default: false)
 */
const Dialog: React.FC<DialogProps> = ({
                                           open,
                                           onClose,
                                           showDefaultHeader = true,
                                           children,
                                           size,
                                           confirmOnClose = false,
                                       }) => {
    const dialogRef = useRef<HTMLDialogElement | null>(null);
    const [show, setShow] = useState<boolean>(false);
    const t = useTrans();
    const [scrolled, setScrolled] = useState<boolean>(false);

    // Attempt to close the dialog, with confirmation if necessary
    const attemptClose = () => {
        if (confirmOnClose) {
            if (window.confirm(t('Are you sure you want to close?'))) {
                onClose();
            }
        } else {
            onClose();
        }
    };

    // Handle backdrop clicks
    const handleBackdropClick = (e: React.MouseEvent<HTMLDialogElement>) => {
        if (e.target === dialogRef.current) {
            attemptClose();
        }
    };

    useEffect(() => {
        const dialog = dialogRef.current;
        if (!dialog) return;

        const onScroll = () => {
            setScrolled(dialog.scrollTop > 5);
        };
        dialog.addEventListener('scroll', onScroll);

        // Handle Escape key
        const handleCancel = (e: Event) => {
            e.preventDefault(); // Prevent default close
            attemptClose();
        };
        dialog.addEventListener('cancel', handleCancel);

        return () => {
            dialog.removeEventListener('scroll', onScroll);
            dialog.removeEventListener('cancel', handleCancel);
        };
    }, [confirmOnClose]);

    useEffect(() => {
        const dialog = dialogRef.current;

        if (open) {
            if (dialog) {
                dialog.showModal();
                setTimeout(() => setShow(true), 10);
            }
            document.body.style.overflow = 'hidden';
        } else {
            if (dialog) {
                setShow(false);
                setTimeout(() => dialog.close(), 300);
            }
            document.body.style.overflow = 'auto';
        }

        return () => {
            document.body.style.overflow = 'auto';
        };
    }, [open]);

    return (
        <dialog
            ref={dialogRef}
            className={`${show ? styles.show : ''} ${styles.modal} ${size ? styles[size] : ''}`}
            onClick={handleBackdropClick}
        >
            <div className={styles.dialogContainer}>
                {showDefaultHeader && (
                    <div className={`${styles.dialogHeader} ${scrolled ? styles.scrolled : ''}`}>
                        <Tooltip content={t('booking.close')}>
                            <Button
                                icon={true}
                                variant="tertiary"
                                aria-label="Close dialog"
                                onClick={attemptClose}
                                className={'default'}
                                size={'sm'}
                            >
                                <FontAwesomeIcon icon={faXmark} size={'lg'} />
                            </Button>
                        </Tooltip>
                    </div>
                )}
                <div className={styles.dialogContent}>{children}</div>
            </div>
        </dialog>
    );
};

export default Dialog;
