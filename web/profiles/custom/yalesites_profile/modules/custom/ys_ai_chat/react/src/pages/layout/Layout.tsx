import { Outlet, Link } from "react-router-dom";
import styles from "./Layout.module.css";
import { CommandBarButton, Dialog, Stack, TextField, ICommandBarStyles, IButtonStyles, DefaultButton  } from "@fluentui/react";
import { useContext, useEffect, useState } from "react";
import { HistoryButton } from "../../components/common/Button";
import { AppStateContext } from "../../state/AppProvider";
import { CosmosDBStatus } from "../../api";

import aiLogo from "../../assets/Logo.svg";
import closeButton from "../../assets/Close.svg";

const Layout = () => {
    const appStateContext = useContext(AppStateContext)
    
    const [isModalOpen, setIsModalOpen] = useState(false);
    
    const handleOpenModal = () => {
        setIsModalOpen(true);
    };
    
    const handleCloseModal = () => {
        setIsModalOpen(false);
    }; 
    
    const handleHistoryClick = () => {
        appStateContext?.dispatch({ type: 'TOGGLE_CHAT_HISTORY' })
    };

    useEffect(() => {}, [appStateContext?.state.isCosmosDBAvailable.status]);

    /**
     * Close modal on escape key press.
     */
    useEffect(() => {
        const close = (e: { key: string; }) => {
          if(e.key === 'Escape'){
            handleCloseModal()
          }
        }
        window.addEventListener('keydown', close)
      return () => window.removeEventListener('keydown', close)
    },[])

    const showHistory = () => {
        appStateContext?.state.isCosmosDBAvailable?.status !== CosmosDBStatus.NotConfigured && 
            <HistoryButton onClick={handleHistoryClick} text={appStateContext?.state?.isChatHistoryOpen ? "Hide chat history" : "Show chat history"}/>    
    }
    return (
    <div className={styles.layout}>
        <section className={styles.modalCallout}>
            <div className={styles.modalCalloutContent}>
                <h2 className="callout__heading">Open the modal dialog</h2>
                <p>
                    Perspiciatis neque delectus voluptatum qui aut veniam voluptatem non.
                </p>
                <p>
                    Officia neque cum iure pariatur asperiores ab et nobis excepturi beatae at itaque sit. Tenetur perspiciatis nesciunt illum ut quisquam est et necessitatibus repellendus qui illo sint. Possimus aliquid quos dolor occaecati at maiores dolore. Qui corrupti animi facilis eos nostrum voluptatem. Perspiciatis ex sed dolor velit. Omnis est voluptatem illo iusto debitis ut sunt deleniti aut repellat.
                </p>
                <button
                type="button"
                onClick={handleOpenModal}
                >
                    Open dialog
                </button>
            </div>
        </section>
        
        {isModalOpen && <Modal onClose={handleCloseModal} />}
    </div>
    );
};

const Modal = ({ onClose }: { onClose: () => void }) => {
    return (
        <section className={styles.modal} aria-modal={"true"} role={"dialog"}>
            <div className={styles.modalContent}>
                <header className={styles.header} role={"banner"}>
                    <Stack horizontal verticalAlign="center" horizontalAlign="space-between" className={styles.headerContainer}>
                        <img src={aiLogo} className={styles.headerTitle} alt="AskYale" />

                        <Stack horizontal>
                            <button className={styles.closeButton} onClick={onClose} aria-label="Close modal">
                                <img src={closeButton} className={styles.closeButtonIcon} alt="Close" />
                            </button>
                        </Stack>
                    </Stack>
                </header>
                <Outlet />
            </div>
        </section>
    );
};

export default Layout;
