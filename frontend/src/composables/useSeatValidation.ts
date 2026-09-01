import type { Seat } from '../types';

export interface OrphanCheckResult {
  isValid: boolean;
  orphanSeats: Seat[];
  errorMessage?: string;
}

/**
 * Composable chuyên trách thuật toán Anti-Orphan Seat (Chống bỏ lại 1 ghế trống đơn lẻ)
 * theo chuẩn rạp chiếu phim quốc tế (CGV, Galaxy, Lotte, AMC).
 */
export function useSeatValidation() {

  /**
   * Kiểm tra xem danh sách ghế đang chọn có tạo ra bất kỳ ghế trống đơn lẻ nào không.
   * @param allSeats Toàn bộ danh sách ghế trong phòng chiếu
   * @param selectedSeats Danh sách ghế mà người dùng đang chọn
   * @param sessionId ID phiên đặt vé hiện tại
   */
  const validateOrphanSeats = (
    allSeats: Seat[],
    selectedSeats: Seat[],
    sessionId: string
  ): OrphanCheckResult => {
    if (selectedSeats.length === 0) {
      return { isValid: true, orphanSeats: [] };
    }

    const orphanSeats: Seat[] = [];
    const selectedIds = new Set(selectedSeats.map(s => s.id));

    // Nhóm ghế theo từng hàng (Row A, B, C...)
    const rowsMap = new Map<string, Seat[]>();
    allSeats.forEach(seat => {
      // Bỏ qua hàng ghế đôi (ví dụ hàng J) vì mỗi ghế đôi đã là 1 sofa độc lập
      if (seat.type === 'couple') return;

      if (!rowsMap.has(seat.row)) {
        rowsMap.set(seat.row, []);
      }
      rowsMap.get(seat.row)!.push(seat);
    });

    for (const [, seatsInRow] of rowsMap.entries()) {
      // Sắp xếp ghế theo thứ tự số tăng dần (1, 2, 3... 14)
      const sortedSeats = [...seatsInRow].sort((a, b) => a.number - b.number);
      const totalInRow = sortedSeats.length;
      if (totalInRow < 3) continue;

      // Xác định trạng thái của từng vị trí trong hàng:
      // true = ĐÃ ĐƯỢC CHỌN / ĐÃ BÁN / ĐANG GIỮ CHỖ
      // false = ĐANG TRỐNG (AVAILABLE)
      const isTaken = (index: number): boolean => {
        if (index < 0 || index >= totalInRow) return true; // Biên ngoài coi như tường/lối đi
        const seat = sortedSeats[index];
        const isUserSelected = selectedIds.has(seat.id);
        const isBooked = seat.status === 'booked';
        const isHeldByOther = seat.status === 'holding' && seat.held_by !== sessionId;
        return isUserSelected || isBooked || isHeldByOther;
      };

      // Tìm các khoảng trống (contiguously empty seats)
      for (let i = 0; i < totalInRow; i++) {
        // Nếu vị trí i đang TRỐNG
        if (!isTaken(i)) {
          const leftTaken = isTaken(i - 1);
          const rightTaken = isTaken(i + 1);

          // Nếu cả bên trái VÀ bên phải đều bị chặn (hoặc là mép hàng) -> Đây là 1 ghế trống đơn độc (Orphan)
          if (leftTaken && rightTaken) {
            const orphanSeat = sortedSeats[i];
            
            // Chỉ bắt lỗi nếu hàng này có ghế mà người dùng ĐANG CHỌN (người dùng gây ra khoảng trống)
            const userHasSelectedInRow = sortedSeats.some(s => selectedIds.has(s.id));
            if (userHasSelectedInRow) {
              orphanSeats.push(orphanSeat);
            }
          }
        }
      }
    }

    if (orphanSeats.length > 0) {
      const firstOrphan = orphanSeats[0];
      const seatNames = orphanSeats.map(s => `${s.row}${s.number}`).join(', ');
      
      let message = `Không thể để trống 1 ghế đơn lẻ [Ghế ${seatNames}]. `;
      if (firstOrphan.number === 1) {
        message += `Vui lòng chọn luôn ghế đầu hàng hoặc chừa trống từ 2 ghế trở lên!`;
      } else {
        message += `Vui lòng chọn ghế liền kề hoặc chừa trống tối thiểu 2 ghế!`;
      }

      return {
        isValid: false,
        orphanSeats,
        errorMessage: message,
      };
    }

    return { isValid: true, orphanSeats: [] };
  };

  return {
    validateOrphanSeats,
  };
}
