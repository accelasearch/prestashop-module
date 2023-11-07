import { useMemo, useState } from "react";
import DataTable from "react-data-table-component";
import FilterComponent from "../components/Filter";
import { useGetLogsQuery } from "../services/service";
import { t } from "../utils";
import Skeleton from "react-loading-skeleton";
import "react-loading-skeleton/dist/skeleton.css";

const ContextDropdown = () => {
  return <div className="">Contesto</div>;
};

const columns = [
  {
    name: "ID",
    maxWidth: "50px",
    selector: (row) => row.id,
  },
  {
    name: "Messaggio",
    selector: (row) => row.message,
    wrap: true,
  },
  {
    name: "Gravità",
    maxWidth: "100px",
    selector: (row) => row.gravity,
    sortable: true,
  },
  {
    name: <ContextDropdown />,
    maxWidth: "200px",
    selector: (row) => row.context,
  },
  {
    name: "Creato il",
    maxWidth: "170px",
    selector: (row) => row.created_at,
    sortable: true,
  },
];

export default function Logs() {
  const { data: logsData = { data: { logs: [] } }, isLoading: isFetchingLogs } =
    useGetLogsQuery(null, {
      pollingInterval: 5000,
    });

  const paginationComponentOptions = {
    rowsPerPageText: "Entità per pagina",
    rangeSeparatorText: "di",
    selectAllRowsItem: true,
    selectAllRowsItemText: "Tutti",
  };
  const [filterText, setFilterText] = useState("");
  const [resetPaginationToggle, setResetPaginationToggle] = useState(false);

  const subHeaderComponentMemo = useMemo(() => {
    const handleClear = () => {
      if (filterText) {
        setResetPaginationToggle(!resetPaginationToggle);
        setFilterText("");
      }
    };
    return (
      <FilterComponent
        onFilter={(e) => setFilterText(e.target.value)}
        onClear={handleClear}
        filterText={filterText}
        filterPlaceholder="Cerca per messaggio o contesto..."
      />
    );
  }, [filterText, resetPaginationToggle]);

  const filteredItems = logsData.data.logs.filter(
    (item) =>
      item.message &&
      item.message.toLowerCase().includes(filterText.toLowerCase())
  );

  return (
    <section aria-labelledby="primary-heading" className="">
      <div className="">
        <div className="grid grid-cols-12">
          <div className="sm:flex-auto col-span-9">
            <h1 className="text-xl font-semibold text-gray-900 mb-2">
              {t("Check the system logs")}
            </h1>
          </div>
        </div>
        <div className="flex flex-col">
          <div className="overflow-x-auto">
            <div className="inline-block min-w-full py-2 align-middle">
              <div className="overflow-hidden">
                {isFetchingLogs ? (
                  <div className="min-h-[80vh] flex justify-center items-center">
                    <div className="mt-4 w-full">
                      <Skeleton height={60} />
                      <div className="mt-6">
                        <Skeleton height={28} count={20} />
                      </div>
                    </div>
                  </div>
                ) : (
                  <DataTable
                    columns={columns}
                    data={filteredItems}
                    pagination
                    dense
                    highlightOnHover
                    persistTableHead
                    paginationComponentOptions={paginationComponentOptions}
                    subHeader
                    subHeaderComponent={subHeaderComponentMemo}
                    paginationRowsPerPageOptions={[20, 50, 100]}
                    paginationPerPage={20}
                  />
                )}
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
